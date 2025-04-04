<?php
// DASHBOARD.PHP
require_once 'utils/recuperer_creneaux.php';
require_once 'utils/recuperer_salles.php';
require_once 'utils/recuperer_matieres.php';
require_once 'utils/recuperer_classes.php';

session_start();
if (!isset($_SESSION['token'])) {
    header("Location: http://192.168.8.152/interface_login.php?error=expired");
    exit;
}

$token = $_SESSION['token'];
$username = $_SESSION["username"];

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

$data = json_decode($response, true);
if (!$data) {
    die("Erreur : Impossible de décoder le JSON.");
}

$success_message = "";
if (isset($_SESSION['info_message'])) {
    $success_message = $_SESSION['info_message'];
    unset($_SESSION['info_message']);
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url_reservations);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
]);

$response = curl_exec($ch);
curl_close($ch);

$data_reservations = json_decode($response, true);

$date_actuelle = new DateTime();
$heure_actuelle = $date_actuelle->format('H:i');
$date_jour = $date_actuelle->format('d/m/Y');

$request_reservation = "http://192.168.8.152:8000/reservations/?croissant=true";
$response_reservation = file_get_contents($request_reservation);
$data = json_decode($response_reservation, true);

$creneaux = getCreneaux();
$salles = getSalles();
$matieres = getMatieres();
$classes = getClasses();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pôle Info - Système de Réservation</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
</head>
<body class="bg-gray-50 font-inter text-gray-800 m-0 p-0">
    <!-- Navigation Bar -->
    <header class="bg-primary fixed top-0 w-full py-3 px-4 shadow-md z-10">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center text-white">
                <span class="font-semibold text-2xl">Pôle Info - Réservations</span>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-white flex items-center">
                    <i class="fas fa-user-circle mr-2"></i>
                    <span>Bonjour, <?php echo htmlspecialchars($username); ?></span>
                </div>
                <a href="logout.php" class="no-underline">
                    <button class="bg-white text-primary font-semibold py-2 px-4 rounded hover:bg-blue-50 transition-colors flex items-center">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Se déconnecter
                    </button>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-6 mt-16">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Tableau de bord</h1>
            <p class="text-gray-600">Gérez vos réservations de salles et consultez le planning.</p>
        </div>

        <!-- Date and Time Display -->
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

        <?php if ($success_message): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <p><?php echo htmlspecialchars($success_message); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Reservation Form Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 overflow-hidden">
            <div class="bg-primary text-white p-3 font-semibold text-lg rounded-t-lg flex items-center">
                <i class="fas fa-plus-circle mr-2"></i>
                Nouvelle réservation
            </div>
            <div class="p-4">
            <form action="ajout_reservation.php" method="post">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="salle" class="block text-sm font-medium text-gray-700 mb-1">Salle</label>
                        <select name="salle" id="salle" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                            <option value="">--Sélectionnez une salle--</option>
                                <?php foreach ($salles as $salle) {
                                    echo "<option value='{$salle['numero']}'>{$salle['numero']}</option>";
                                    }
                                ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="matiere" class="block text-sm font-medium text-gray-700 mb-1">Matière</label>
                        <select name="matiere" id="matiere" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                            <option value="">--Sélectionnez une matière--</option>
                            <?php foreach ($matieres as $matiere) {
                                    echo "<option value='{$matiere['nom']}'>{$matiere['nom']}</option>";
                                    }
                            ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="classe" class="block text-sm font-medium text-gray-700 mb-1">Classe/groupe</label>
                        <select name="classe[]" id="classe" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" multiple required>
                            <?php foreach ($classes as $classe) {
                                    echo "<option value='{$classe['nom']}'>{$classe['nom']}</option>";
                                    }
                            ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="date_reserv" class="block text-sm font-medium text-gray-700 mb-1">Date de réservation</label>
                        <input type="date" id="date_reserv" name="date_reserv" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                    </div>

                    <div class="col-span-1 md:col-span-2 mb-4">
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Informations sur le cours/activité (optionnel)</label>
                        <textarea id="message" name="message" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="Détails supplémentaires sur le cours ou l'activité..."></textarea>
                    </div>
                    <div class="col-span-1 md:col-span-2 mb-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="startTime" class="block text-sm font-medium text-gray-700 mb-1">Heure de début</label>
                                <select id="startTime" name="startTime" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                                    <option value="">Sélectionnez une heure</option>
                                    <?php 
                                        foreach ($creneaux as $creneau) {
                                            echo "<option value='$creneau'>$creneau</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                            <div>
                                <label for="duration" class="block text-sm font-medium text-gray-700 mb-1">Durée</label>
                                <select id="duration" name="duration" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                                    <option value="">Sélectionnez une durée</option>
                                    <option value="0.83">50 minutes (1 heure)</option>
                                    <option value="1.67">1 heure 40 (2 heures)</option>
                                    <option value="2.5">2 heures 30 (3 heures)</option>
                                    <option value="3.33">3 heures 20 (4 heures)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-1 md:col-span-2 flex justify-end">
                        <button type="submit" class="bg-primary text-white font-semibold py-2 px-4 rounded-md hover:bg-blue-700 transition-colors flex items-center">
                            <i class="fas fa-save mr-2"></i>
                            Enregistrer la réservation
                        </button>
                    </div>
                </div>
            </form>

            </div>
        </div>
        <!-- Footer -->
        <footer class="text-center text-sm text-gray-500 mt-8 border-t border-gray-200 pt-4">
            © 2025 Système d'information BTS - Tous droits réservés
        </footer>
    </main>
    <script>
        function toggleDetails(id) {
            document.getElementById(id).classList.toggle('hidden');
        }

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
</body>
</html>