<?php
session_start();
if (!isset($_SESSION['token']) and $_SESSION['type_compte']!=1) {
    header("Location: http://127.0.0.1/interface_login.php?error=expired");
    exit;
}

$token = $_SESSION['token'];
$api_url_verify = "http:/127.0.0.1:8000/verify-token/";
$api_url_reservations = "http://127.0.0.1:8000/reservations/";

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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestion</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    
    <header class="bg-indigo-600 text-white p-4 flex justify-between items-center w-full fixed top-0 left-0 right-0 shadow-md">
        <h1 class="text-xl font-bold">Système d'information BTS - Administration</h1>
        <div class="flex gap-4">
            <a href="../index.php" class="bg-white px-4 py-2 rounded-md text-indigo-600 hover:bg-indigo-50 shadow-md font-semibold">Tableau d'accueil</a>
            <a href="../user/dashboard.php" class="bg-white px-4 py-2 rounded-md text-indigo-600 hover:bg-indigo-50 shadow-md font-semibold">Ajout Réservation</a>
            <a href="../user/logout.php" class="bg-white px-4 py-2 rounded-md text-red-600 hover:bg-red-50 shadow-md font-semibold">Déconnexion</a>
        </div>
    </header>
    
    <div class="container mx-auto px-4 mt-32">
        <div class="grid grid-cols-2 gap-8">
            <div class="bg-white shadow-lg p-6 rounded-lg">
                <h2 class="text-2xl font-bold mb-4">Ajouts d'Utilisateurs</h2>
                <form class="space-y-4">
                    <input type="text" placeholder="Prénom" class="w-full p-2 border rounded-md">
                    <input type="text" placeholder="Nom" class="w-full p-2 border rounded-md">
                    <input type="password" placeholder="Mot de passe" class="w-full p-2 border rounded-md">
                    <select name="choix_droit" class="w-full p-2 border rounded-md">
                        <option value="Administrateur">Administrateur</option>
                        <option value="Utilisateur">Utilisateur</option>
                    </select>
                    <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-green-600 w-full">Créer Utilisateur</button>
                </form>
            </div>
            
            <div class="bg-white shadow-lg p-6 rounded-lg">
                <h2 class="text-2xl font-bold mb-4">Suppressions d'Utilisateurs</h2>
                <form class="space-y-4">
                    <select name="sup_user" class="w-full p-2 border rounded-md">
                        <option value=""></option>
                    </select>
                    <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-red-600 w-full">Supprimer Utilisateur</button>
                </form>
            </div>
        </div>
        
        <div class="bg-indigo-600 text-white text-xl font-bold p-4 rounded-lg my-6 shadow-lg">
            Gestion des ressources
        </div>
        
        <div class="grid grid-cols-4 gap-4">
            <div class="bg-white shadow-lg p-6 rounded-lg">
                <h2 class="text-2xl font-bold mb-4">Gestion des Matières</h2>
                <form class="space-y-4">
                    <input type="text" placeholder="Nom de la matière" class="w-full p-2 border rounded-md">
                    <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-green-600 w-full">Ajouter Matière</button>
                </form>
                <form class="mt-10 space-y-4">
                    <select name="sup_matiere" class="w-full p-2 border rounded-md">
                        <option value=""></option>
                    </select>
                    <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-red-600 w-full">Supprimer Matière</button>
                </form>
            </div>
            
            <div class="bg-white shadow-lg p-6 rounded-lg">
                <h2 class="text-2xl font-bold mb-4">Gestion des Créneaux</h2>
                <form class="space-y-4">
                    <input type="text" placeholder="00:00" class="w-full p-2 border rounded-md">
                    <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-green-600 w-full">Ajouter Créneau</button>
                </form>
                <form class="mt-10 space-y-4">
                    <select name="creneau" class="w-full p-2 border rounded-md">
                        <?php
                        $get_creneau = "http://192.168.8.152:8000/creneaux/";
                        $reponse_creneau = file_get_contents($get_creneau);
                        $data_creneau = json_decode($reponse_creneau, true);
                        
                        if (is_array($data_creneau)) {
                            foreach ($data_creneau as $item) {
                                $creneau_value = htmlspecialchars($item['creneau']);
                                echo "<option value='$creneau_value'>$creneau_value</option>";
                            }
                        }
                        ?>
                    </select>
                    <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-red-600 w-full">Supprimer Créneau</button>
                </form>
            </div>
            
            <div class="bg-white shadow-lg p-6 rounded-lg">
                <h2 class="text-2xl font-bold mb-4">Gestion des Classes</h2>
                <form class="space-y-4">
                    <input type="text" placeholder="Nom de la classe" class="w-full p-2 border rounded-md">
                    <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-green-600 w-full">Ajouter Classe</button>
                </form>
                <form class="mt-10 space-y-4">
                    <select name="sup_classe" class="w-full p-2 border rounded-md">
                        <option value=""></option>
                    </select>
                    <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-red-600 w-full">Supprimer Classe</button>
                </form>
            </div>
            
            <div class="bg-white shadow-lg p-6 rounded-lg">
                <h2 class="text-2xl font-bold mb-4">Gestion des Salles</h2>
                <form class="space-y-4">
                    <input type="text" placeholder="Nom ou numéro de la salle" class="w-full p-2 border rounded-md">
                    <input type="text" placeholder="Type de salle (TP-info, Cours, etc.)" class="w-full p-2 border rounded-md">
                    <input type="number" placeholder="Capacité de la salle" class="w-full p-2 border rounded-md">
                    <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-green-600 w-full">Ajouter Salle</button>
                </form>
                <form class="mt-10 space-y-4">
                    <select name="sup_salle" class="w-full p-2 border rounded-md">
                        <option value=""></option>
                    </select>
                    <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-red-600 w-full">Supprimer Salle</button>
                </form>
            </div>
        </div>
    </div>
    
    <footer class="mt-6 text-center text-sm text-gray-500">
        <p>© 2025 Système d'information BTS - Tous droits réservés</p>
    </footer>
</body>
</html>
