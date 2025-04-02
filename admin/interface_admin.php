<?php
session_start();
if (!isset($_SESSION['token']) and $_SESSION['type_compte']!=1) {
    header("Location: http://192.168.8.152/interface_login.php?error=expired");
    exit;
}

$token = $_SESSION['token'];
$api_url_verify = "http://192.168.8.152:8000/verify-token/";
$api_url_reservations = "http://192.168.8.152:8000/reservations/";

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
    header("Location: http://192.168.8.152/interface_login.php?error=expired");
    exit;
}

if ($http_code != 200) {
    session_destroy();
    header("Location: http://192.168.8.152/interface_login.php?error=expired");
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white shadow-sm p-6 rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
                <h2 class="text-xl font-semibold mb-4 text-primary">Ajouts d'Utilisateurs</h2>
                <form class="space-y-4">
                    <input type="text" placeholder="Prénom" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                    <input type="text" placeholder="Nom" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                    <input type="password" placeholder="Mot de passe" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                    <select name="choix_droit" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                        <option value="Administrateur">Administrateur</option>
                        <option value="Utilisateur">Utilisateur</option>
                    </select>
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors w-full font-medium">Créer Utilisateur</button>
                </form>
            </div>
            
            <div class="bg-white shadow-sm p-6 rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
                <h2 class="text-xl font-semibold mb-4 text-primary">Suppressions d'Utilisateurs</h2>
                <form class="space-y-4">
                    <select name="sup_user" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                        <option value=""></option>
                    </select>
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
                <form class="space-y-4">
                    <input type="text" placeholder="Nom de la matière" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" required>
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-green-600 transition-colors w-full font-medium">Ajouter Matière</button>
                </form>
                <form class="mt-6 space-y-4">
                    <select name="sup_matiere" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                        <option value=""></option>
                    </select>
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-red-600 transition-colors w-full font-medium">Supprimer Matière</button>
                </form>
            </div>
            
            <div class="bg-white shadow-sm p-6 rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
                <h2 class="text-xl font-semibold mb-4 text-primary">Gestion des Créneaux</h2>
                <form class="space-y-4">
                    <input type="text" placeholder="00:00" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" required>
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-green-600 transition-colors w-full font-medium">Ajouter Créneau</button>
                </form>
                <form class="mt-6 space-y-4">
                    <select name="creneau" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                        <?php
                        $get_creneau = "http://192.168.8.152:8000/creneaux/";
                        $reponse_creneau = file_get_contents($get_creneau);
                        $data_creneau = json_decode($reponse_creneau, true);
                        
                        if (is_array($data_creneau)) {
                            foreach ($data_creneau as $item) {
                                $interval = new DateInterval($item['heure_debut']);
                                $heures = $interval->h;
                                $minutes = $interval->i;
                                $creneau = sprintf("%02d:%02d", $heures, $minutes);
                                echo "<option value='$creneau'>$creneau</option>";
                            }
                        }
                        ?>
                    </select>
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-red-600 transition-colors w-full font-medium">Supprimer Créneau</button>
                </form>
            </div>
            
            <div class="bg-white shadow-sm p-6 rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
                <h2 class="text-xl font-semibold mb-4 text-primary">Gestion des Classes</h2>
                <form class="space-y-4">
                    <input type="text" placeholder="Nom de la classe" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" required>
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-green-600 transition-colors w-full font-medium">Ajouter Classe</button>
                </form>
                <form class="mt-6 space-y-4">
                    <select name="sup_classe" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                        <option value=""></option>
                    </select>
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-red-600 transition-colors w-full font-medium">Supprimer Classe</button>
                </form>
            </div>
            
            <div class="bg-white shadow-sm p-6 rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
                <h2 class="text-xl font-semibold mb-4 text-primary">Gestion des Salles</h2>
                <form class="space-y-4">
                    <input type="text" placeholder="Nom ou numéro de la salle" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" required>
                    <input type="text" placeholder="Type de salle (TP-info, Cours, etc.)" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" required>
                    <input type="number" placeholder="Capacité de la salle" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" required>
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-green-600 transition-colors w-full font-medium">Ajouter Salle</button>
                </form>
                <form class="mt-10 space-y-4">
                    <select name="sup_salle" class="w-full p-2 border rounded-md">
                        <?
                        $get_salle = "http://192.168.8.152:8000/salles/";
                        $response_salle = file_get_contents($get_salle);
                        $data_salle = json_decode($response_salle, true);
                        if(array($data_creneau))
                        {
                        foreach($data_salle as $item)
                        {
                            $salle=$item['numero'];
                            echo "<option value='$salle'>$salle</option>";
                        }
                        }
                        ?>
                    </select>
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-red-600 transition-colors w-full font-medium">Supprimer Salle</button>
                </form>
            </div>
        </div>

        <footer class="text-center text-sm text-gray-500 mt-8 border-t border-gray-200 pt-4">
            © 2025 Système d'information BTS - Tous droits réservés
        </footer>
    </main>
</body>
</html>