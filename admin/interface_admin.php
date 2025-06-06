<?php
require_once __DIR__ . '/../config.php';
session_start();

$success_message = "";
if (isset($_SESSION['info_message'])) {
    $success_message = $_SESSION['info_message'];
    unset($_SESSION['info_message']);
}

if (!isset($_SESSION['token']) or $_SESSION['type_compte']!=1) {
    header('Location:' . getWebUrl('dashboard.php'));
    exit;
}

$token = $_SESSION['token'];
$api_url_verify = getApiUrl("/verify-token/");
$api_url_reservations = getApiUrl("/reservations/");

$ch = curl_init();
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_URL, $api_url_verify);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($http_code != 200 || !$response) {
    error_log("Erreur de vérification du token : HTTP $http_code - $curl_error");
    session_destroy();
    header('Location:' . getWebUrl('interface_login.php?error=expired'));
    exit;
}

if ($http_code != 200) {
    session_destroy();
    header('Location:' . getWebUrl('interface_login.php?error=expired'));
    exit;
}

$date_actuelle = new DateTime();
$heure_actuelle = $date_actuelle->format('H:i');
$date_jour = $date_actuelle->format('d/m/Y');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Système d'information BTS</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { primary: { DEFAULT: '#1a4d85', light: '#e6f0ff' } },
                    fontFamily: { inter: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        .clock-display {
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.5px;
        }
    </style>
    <script>
        function updateClock() {
            const now = new Date();
            const time = [now.getHours(), now.getMinutes(), now.getSeconds()]
                .map(n => n.toString().padStart(2, '0'))
                .join(':');
            document.getElementById('clock').textContent = time;
            setTimeout(updateClock, 1000);
        }

        window.onload = updateClock;
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-50 font-inter text-gray-800 m-0 p-0">
    
    <header class="bg-primary fixed top-0 w-full py-3 px-4 shadow-md z-10">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center">
                <div class="bg-white p-2 rounded-lg mr-3">
                    <img src="../logo.png" alt="Logo Pole Info" class="h-9">
                </div>
                <div class="font-semibold text-white text-2xl">Système d'information BTS - Administration</div>
            </div>
            <div class="flex gap-3">
                <a href="../index.php" class="no-underline">
                    <button class="bg-white text-primary font-semibold py-2 px-4 rounded hover:bg-blue-50 transition-colors">
                        Tableau d'accueil
                    </button>
                </a>
                <a href="../user/dashboard.php" class="no-underline">
                    <button class="bg-white text-primary font-semibold py-2 px-4 rounded hover:bg-blue-50 transition-colors">
                        Ajout Réservation
                    </button>
                </a>
                <a href="../user/logout.php" class="no-underline">
                    <button class="bg-white text-red-600 font-semibold py-2 px-4 rounded hover:bg-red-50 transition-colors">
                        Déconnexion
                    </button>
                </a>
            </div>
        </div>
    </header>
    
    <main class="container mx-auto px-4 py-6 mt-24">
        <div class="bg-white p-3 mb-6 rounded-lg shadow-sm border border-gray-200 flex justify-between items-center">
            <div class="text-gray-600">
                <span class="font-medium">Aujourd'hui : </span>
                <span><?php echo $date_jour; ?></span>
            </div>
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium text-primary mr-2">Heure actuelle :</span>
                <span id="clock" class="clock-display font-medium bg-primary text-white px-3 py-1 rounded-md">
                    <?php echo $heure_actuelle; ?>
                </span>
            </div>
        </div>
        <?php if ($success_message) { ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <p><?php echo htmlspecialchars($success_message); ?></p>
                    
                </div>
            </div>
        <?php } ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white shadow-sm p-6 rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
                <h2 class="text-xl font-semibold mb-4 text-primary">Ajouts d'Utilisateurs</h2>
                <form class="space-y-4" action="traitement-admin.php" method="POST" >
                    <input type="text" name="prenom" placeholder="Prénom" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" require>
                    <input type="text" name="nom" placeholder="Nom" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" require>
                    <input type="password" name="password" placeholder="Mot de passe" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" require>
                    <select name="choix_droit"  name="type" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                        <option value="1" >Administrateur</option>
                        <option value="0" >Utilisateur</option>
                    </select>
                    <input type="hidden" name="action" value="ajouter_utilisateur">
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors w-full font-medium">Créer Utilisateur</button>
                </form>
            </div>
            
            <div class="bg-white shadow-sm p-6 rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
                <h2 class="text-xl font-semibold mb-4 text-primary">Suppressions d'Utilisateurs</h2>
                <form class="space-y-4" action="traitement-admin.php" method="POST">
                    <select name="sup_user" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                        <?php
                            $get_user = getApiUrl("/utilisateurs/");
                            $response_user = file_get_contents($get_user);
                            $data_user = json_decode($response_user, true);
                            foreach($data_user as $item)
                            {
                                $user=$item['login'];
                                $nom_user=$item['nom'];
                                $prenom_user=$item['prenom'];
                                $type_user=$item['type'];
                                if($type_user==1){
                                    $type='Administrateur';
                                }
                                else{
                                    $type='Utilisateur';
                                }
                                echo "<option value='$user'>$user | $prenom_user - $nom_user | $type</option>";
                            }
                        ?>
                    </select>
                    <input type="hidden" name="action" value="supprimer_utilisateur">
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-red-600 transition-colors w-full font-medium">Supprimer Utilisateur</button>
                </form>
            </div>
        </div>
        
        <div class="bg-primary text-white p-3 mb-4 font-semibold text-lg rounded-lg shadow">
            Gestion des ressources
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white shadow-sm p-6 rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
                <h2 class="text-xl font-semibold mb-4 text-primary">Gestion des Matières</h2>
                <form action="traitement-admin.php" method="POST" class="space-y-4">
                    <input name="add_matiere"type="text" pattern="^[A-Za-zÀ-ÿ\- ]+$" placeholder="Nom de la matière" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" required>
                    <input type="hidden" name="action" value="ajouter_matiere">
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-green-600 transition-colors w-full font-medium">Ajouter Matière</button>
                </form>
                <form class="mt-6 space-y-4" action="traitement-admin.php" method="POST">
                    <select name="sup_matiere" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                    <?php
                        $get_matiere = getApiUrl("/matieres/");
                        $response_matiere = file_get_contents($get_matiere);
                        $data_matiere = json_decode($response_matiere, true);
                        foreach($data_matiere as $item)
                        {
                            $matiere=$item['nom'];
                            echo "<option value='$matiere'>$matiere</option>";
                        }
                        ?>
                    </select>
                    <input type="hidden" name="action" value="supprimer_matiere">
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-red-600 transition-colors w-full font-medium">Supprimer Matière</button>
                </form>
            </div>
            
            <div class="bg-white shadow-sm p-6 rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
                <h2 class="text-xl font-semibold mb-4 text-primary">Gestion des Créneaux</h2>
                <form action="traitement-admin.php" method="POST" class="space-y-4">
                    <input type="text" name="add_creneau" pattern="^([01][0-9]|2[0-3]):[0-5][0-9]$" placeholder="00:00" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" required>
                    <input type="hidden" name="action" value="ajouter_creneau">
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-green-600 transition-colors w-full font-medium">Ajouter Créneau</button>
                </form>
                <form class="mt-6 space-y-4" action="traitement-admin.php" method="POST">
                    <select name="sup_creneau" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                        <?php
                        $get_creneau = getApiUrl("/creneaux/");
                        $reponse_creneau = file_get_contents($get_creneau);
                        $data_creneau = json_decode($reponse_creneau, true);
                        
                        if (is_array($data_creneau)) {
                            $creneau=[];
                            foreach ($data_creneau as $item) {
                                $interval = new DateInterval($item['heure_debut']);
                                $heures = $interval->h;
                                $minutes = $interval->i;
                                $creneau[]= sprintf("%02d:%02d", $heures, $minutes);
                            }
                            sort($creneau);
                            foreach($creneau as $creneau){
                                echo "<option value='$creneau'>$creneau</option>";
                            }
                        }
                        ?>
                    </select>
                    <input type="hidden" name="action" value="supprimer_creneau">
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-red-600 transition-colors w-full font-medium">Supprimer Créneau</button>
                </form>
            </div>
            
            <div class="bg-white shadow-sm p-6 rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
                <h2 class="text-xl font-semibold mb-4 text-primary">Gestion des Classes</h2>
                <form action="traitement-admin.php" method="POST" class="space-y-4">
                    <input type="text" name="add_classe" pattern="^[A-Za-z0-9_-]+$" placeholder="Nom de la classe" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" required>
                    <input type="hidden" name="action" value="ajouter_classe">
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-green-600 transition-colors w-full font-medium">Ajouter Classe</button>
                </form>
                <form class="mt-6 space-y-4" action="traitement-admin.php" method="POST">
                    <select name="sup_classe" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                    <?php
                        $get_classe = getApiUrl("/classes/");
                        $response_classe = file_get_contents($get_classe);
                        $data_classe = json_decode($response_classe, true);
                        foreach($data_classe as $item)
                        {
                            $classe=$item['nom'];
                            echo "<option value='$classe'>$classe</option>";
                        }
                        ?>
                    </select>
                    <input type="hidden" name="action" value="supprimer_classe">
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-red-600 transition-colors w-full font-medium">Supprimer Classe</button>
                </form>
            </div>
            
            <div class="bg-white shadow-sm p-6 rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
                <h2 class="text-xl font-semibold mb-4 text-primary">Gestion des Salles</h2>
                <form action="traitement-admin.php" method="POST" class="space-y-4">
                    <input pattern="^[A-Za-z0-9]+$" name="add_salle1" type="text" placeholder="Nom ou numéro de la salle" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" required>
                    <input pattern="^[A-Za-zÀ-ÿ\s\-$]+$" name="add_salle2" type="text" placeholder="Type de salle (TP-info, Cours, etc.)" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" required>
                    <input pattern="^[0-9]+$" name="add_salle3" type="number" placeholder="Capacité de la salle" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" required>
                    <input type="hidden" name="action" value="ajouter_salle">
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-green-600 transition-colors w-full font-medium">Ajouter Salle</button>
                </form>
                <form class="mt-10 space-y-4" action="traitement-admin.php" method="POST">
                    <select name="sup_salle" class="w-full p-2 border rounded-md">
                        <?php
                        $get_salle = getApiUrl("/salles/");
                        $response_salle = file_get_contents($get_salle);
                        $data_salle = json_decode($response_salle, true);
                        foreach($data_salle as $item)
                        {
                            $salle=$item['numero'];
                            echo "<option value='$salle'>$salle</option>";
                        }
                        ?>
                    </select>
                    <input type="hidden" name="action" value="supprimer_salle">
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-red-600 transition-colors w-full font-medium">Supprimer Salle</button>
                </form>
            </div>
        </div>
        <div class="bg-primary text-white p-3 mt-12 font-semibold text-lg rounded-lg shadow">
    Gestion des sauvegardes de la base de données
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
    <!-- Création de backup -->
    <div class="bg-white shadow-sm p-6 rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
        <h2 class="text-xl font-semibold mb-4 text-primary">Créer une sauvegarde</h2>
        <form action="backup-db.php" method="POST">
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-green-600 transition-colors w-full font-medium">Télécharger une sauvegarde</button>
        </form>
    </div>

    <!-- Restauration de backup -->
    <div class="bg-white shadow-sm p-6 rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
        <h2 class="text-xl font-semibold mb-4 text-primary">Restaurer une sauvegarde</h2>
        <form action="restore-db.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="sql_file" accept=".sql" required class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary mb-3" require>
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors w-full font-medium">Restaurer la base</button>
        </form>
        <div class="alert alert-warning">
        <strong>Attention!</strong> Cette action remplacera toutes les données actuelles. Assurez-vous d'avoir une sauvegarde récente avant de procéder.
        </div>
    </div>
</div>


        <footer class="text-center text-sm text-gray-500 mt-8 border-t border-gray-200 pt-4">
            © 2025 Système d'information BTS - Tous droits réservés
        </footer>
    </main>
</body>
</html>