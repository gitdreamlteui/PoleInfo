<?php
// DASHBOARD.PHP
require_once __DIR__ . '/../config.php';
require_once 'utils/recuperer_creneaux.php';
require_once 'utils/recuperer_salles.php';
require_once 'utils/recuperer_matieres.php';
require_once 'utils/recuperer_classes.php';
require_once 'utils/recuperer_reservation.php';

session_start();
if (!isset($_SESSION['token'])) {
    header("Location: " . getWebUrl('interface_login.php?error=expired'));
    exit;
}

$token = $_SESSION['token'];
$username = $_SESSION['username'];
$login = $_SESSION['login'];
$type = $_SESSION['type_compte'];

$api_url_verify = getApiUrl('/verify-token/');
$api_url_reservations = getApiUrl('/reservations/');

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
    header("Location: " . getWebUrl('interface_login.php?error=expired'));
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
$error_message = "";
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
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

$request_reservation = getApiUrl('/reservations/?croissant=true');
$response_reservation = file_get_contents($request_reservation);
$data = json_decode($response_reservation, true);

$creneaux = getCreneaux();
$salles = getSalles();
$matieres = getMatieres();
$classes = getClasses();

if ($type == 1) {
    $reservations = getReservations();
}
elseif ($type == 0) {
    $reservations = getReservations($username);
}

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
        <a href="../index.php">
            <button class="bg-gray-200 text-gray-800 font-semibold py-2 px-5 rounded-md hover:bg-gray-300 transition-colors flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Retour au menu principal
            </button>
        </a>
        <?php if ($type === 1) :?>
        <a href="../admin/interface-admin.php">
            <button class="bg-gray-200 text-gray-800 font-semibold py-2 px-5 rounded-md hover:bg-gray-300 transition-colors flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Interface d'administration
            </button>
        </a>
        <?php endif; ?>
    </div>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Tableau de bord</h1>
            <p class="text-gray-600">Gérez vos réservations de salles et consultez le planning.</p>
        </div>
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
        <?php if ($error_message): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
                <div class="flex items-center">
                    <i class="fa fa-exclamation-circle mr-2"></i>
                    <p><?php echo htmlspecialchars($error_message); ?></p>
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
                                    <option value="0.84">50 minutes</option>
                                    <option value="1.67">1 heure 40</option>
                                    <option value="2.5">2 heures 30</option>
                                    <option value="3.33">3 heures 20</option>
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

        <!-- Réservations existantes -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 overflow-hidden">
            <div class="bg-primary text-white p-3 font-semibold text-lg rounded-t-lg flex items-center">
                <i class="fas fa-calendar-alt mr-2"></i>
                Mes réservations
            </div>
            
            <div class="p-4">
                <?php if (empty($reservations)): ?>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-calendar-times text-4xl mb-3"></i>
                    <p>Aucune réservation n'a été trouvée.</p>
                </div>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horaire</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Salle</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matière</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Classe(s)</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Professeur</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($reservations as $reservation): 
                                // Convertir l'heure de début en format lisible
                                $heure_debut = preg_replace('/^PT(\d+)H(?:(\d+)M)?$/', '$1:$2', $reservation['heure_debut']);
                                $heure_debut = str_replace(':','h',$heure_debut);
                                if (substr($heure_debut, -1) === 'h') $heure_debut .= '00';
                            ?>
                            <tr class="hover:bg-primary-light transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo date('d/m/Y', strtotime($reservation['date'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $heure_debut; ?> (<?php $reservation['duree']; ?>)
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-md">
                                        <?php echo $reservation['numero_salle']; ?>
                                    </span>
                                    <span class="text-xs text-gray-500 block mt-1"><?php echo $reservation['type_salle']; ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $reservation['nom_matiere']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php 
                                        $classes = explode(', ', $reservation['noms_classes']);
                                        foreach ($classes as $classe) {
                                            echo "<span class='inline-block px-2 py-1 bg-green-100 text-green-800 rounded-md mr-1 mb-1'>$classe</span>";
                                        }
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $reservation['prenom'] . ' ' . $reservation['nom_user']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button onclick="toggleDetails('details-<?php echo $reservation['id_reservation']; ?>')" class="text-primary hover:text-blue-800 mr-3">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                    <?php if ($reservation['nom_user'] === $username || $type === 1): ?>
                                    <a href="edit.php?id=<?php echo $reservation['id_reservation']; ?>" class="text-indigo-600 hover:text-indigo-800 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="supprimer_reservation.php?id=<?php echo $reservation['id_reservation']; ?>" class="text-red-600 hover:text-red-800" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette réservation ?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                                
                            </tr>
                            <tr id="details-<?php echo $reservation['id_reservation']; ?>" class="hidden bg-gray-50">
                                <td colspan="7" class="px-6 py-4 text-sm text-gray-500">
                                    <div class="flex items-start">
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-700 mb-1">Informations supplémentaires :</div>
                                            <p><?php echo !empty($reservation['info']) ? nl2br(htmlspecialchars($reservation['info'])) : 'Aucune information supplémentaire'; ?></p>
                                        </div>
                                        <div class="ml-8">
                                            <div class="font-medium text-gray-700 mb-1">Détails de la salle :</div>
                                            <p>Capacité : <?php echo $reservation['capacite_salle']; ?> personnes</p>
                                            <p>Type : <?php echo $reservation['type_salle']; ?></p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
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
