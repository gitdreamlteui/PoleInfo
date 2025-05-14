<?php
// edit.PHP
require_once __DIR__ . '/../config.php';
require_once 'utils/recuperer_creneaux.php';
require_once 'utils/recuperer_salles.php';
require_once 'utils/recuperer_matieres.php';
require_once 'utils/recuperer_classes.php';

session_start();
if (!isset($_SESSION['token'])) {
    header("Location: " . getWebUrl('interface_login.php?error=expired'));
    exit;
}

$token = $_SESSION['token'];
$username = $_SESSION['username'];
$login = $_SESSION['login'];
$type = $_SESSION['type_compte'];

// Récupération de l'ID de la réservation depuis l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['info_message'] = "Aucune réservation spécifiée pour modification.";
    header("Location: dashboard.php");
    exit;
}

$id_reservation = intval($_GET['id']);

// Récupération des données de la réservation spécifique
$api_url_reservation = getApiUrl("/reservations/?reservation_id=$id_reservation");

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url_reservation);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code != 200 || !$response) {
    $_SESSION['info_message'] = "Erreur lors de la récupération des données de la réservation.";
    header("Location: dashboard.php");
    exit;
}

$reservation_json = json_decode($response, true);
$reservation = $reservation_json[0];

$creneaux = getCreneaux();
$salles = getSalles();
$matieres = getMatieres();
$classes = getClasses();

// Formatage des données pour l'affichage
$date_reservation = date('Y-m-d', strtotime($reservation['date']));


// Correction du traitement de l'heure de début
$heure_debut_raw = $reservation['heure_debut']; // par exemple "PT9H"
// Convertir l'heure de début en format lisible avec le code existant
$heure_debut = preg_replace('/^PT(\d+)H(?:(\d+)M)?$/', '$1:$2', $heure_debut_raw);
$heure_debut = str_replace(':','h',$heure_debut);
if (substr($heure_debut, -1) === 'h') $heure_debut .= '00';
// Maintenant, pour les créneaux, appliquez la même logique pour les comparer
$creneaux_formattés = [];
foreach ($creneaux as $creneau) {
    // Si le créneau est déjà formaté, on le garde tel quel
    if (strpos($creneau, 'h') !== false) {
        $creneaux_formattés[] = $creneau;
    } 
    // Sinon, s'il est au format ISO, on le formate
    else if (strpos($creneau, 'PT') === 0) {
        $creneau_formatté = preg_replace('/^PT(\d+)H(?:(\d+)M)?$/', '$1:$2', $creneau);
        $creneau_formatté = str_replace(':','h',$creneau_formatté);
        if (substr($creneau_formatté, -1) === 'h') $creneau_formatté .= '00';
        $creneaux_formattés[] = $creneau_formatté;
    }
    // Cas imprévu - on garde la valeur originale
    else {
        $creneaux_formattés[] = $creneau;
    }
}


$classes_reservees = explode(', ', $reservation['noms_classes']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la réservation - Pôle Info</title>
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
            <a href="dashboard.php">
                <button class="bg-gray-200 text-gray-800 font-semibold py-2 px-5 rounded-md hover:bg-gray-300 transition-colors flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Retour au tableau de bord
                </button>
            </a>
        </div>

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Modifier la réservation</h1>
                <p class="text-gray-600">Modifiez les détails de votre réservation.</p>
            </div>
        </div>

        <!-- Reservation Form Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 overflow-hidden">
            <div class="bg-primary text-white p-3 font-semibold text-lg rounded-t-lg flex items-center">
                <i class="fas fa-edit mr-2"></i>
                Modifier la réservation #<?php echo $id_reservation; ?>
            </div>
            <div class="p-4">
                <form action="modifier_reservation.php" method="post">
                    <input type="hidden" name="id_reservation" value="<?php echo $id_reservation; ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label for="salle" class="block text-sm font-medium text-gray-700 mb-1">Salle</label>
                            <select name="salle" id="salle" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                                <option value="">--Sélectionnez une salle--</option>
                                <?php foreach ($salles as $salle): ?>
                                    <option value="<?php echo $salle['numero']; ?>" <?php echo ($salle['numero'] == $reservation['numero_salle']) ? 'selected' : ''; ?>>
                                        <?php echo $salle['numero']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label for="matiere" class="block text-sm font-medium text-gray-700 mb-1">Matière</label>
                            <select name="matiere" id="matiere" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                                <option value="">--Sélectionnez une matière--</option>
                                <?php foreach ($matieres as $matiere): ?>
                                    <option value="<?php echo $matiere['nom']; ?>" <?php echo ($matiere['nom'] == $reservation['nom_matiere']) ? 'selected' : ''; ?>>
                                        <?php echo $matiere['nom']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="classe" class="block text-sm font-medium text-gray-700 mb-1">Classe/groupe</label>
                            <select name="classe[]" id="classe" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" multiple required>
                                <?php foreach ($classes as $classe): ?>
                                    <option value="<?php echo $classe['nom']; ?>" <?php echo (in_array($classe['nom'], $classes_reservees)) ? 'selected' : ''; ?>>
                                        <?php echo $classe['nom']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="date_reserv" class="block text-sm font-medium text-gray-700 mb-1">Date de réservation</label>
                            <input type="date" id="date_reserv" name="date_reserv" value="<?php echo $date_reservation; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                        </div>

                        <div class="col-span-1 md:col-span-2 mb-4">
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Informations sur le cours/activité (optionnel)</label>
                            <textarea id="message" name="message" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="Détails supplémentaires sur le cours ou l'activité..."><?php echo htmlspecialchars($reservation['info']); ?></textarea>
                        </div>
                        
                        <div class="col-span-1 md:col-span-2 mb-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="startTime" class="block text-sm font-medium text-gray-700 mb-1">Heure de début</label>
                                    <select id="startTime" name="startTime" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                                        <option value="">Sélectionnez une heure</option>
                                        <?php foreach ($creneaux as $creneau): ?>
                                            <?php 
                                            // Appliquer le même formatage pour chaque créneau lors de la comparaison
                                            $creneau_formatté = preg_replace('/^PT(\d+)H(?:(\d+)M)?$/', '$1:$2', $creneau);
                                            $creneau_formatté = str_replace(':','h',$creneau_formatté);
                                            if (substr($creneau_formatté, -1) === 'h') $creneau_formatté .= '00';
                                            ?>
                                            <option value="<?php echo $creneau; ?>" <?php echo ($creneau_formatté === $heure_debut) ? 'selected' : ''; ?>>
                                                <?php echo $creneau_formatté; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label for="duration" class="block text-sm font-medium text-gray-700 mb-1">Durée</label>
                                    <select id="duration" name="duration" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                                        <option value="">Sélectionnez une durée</option>
                                        <option value="0.84" <?php echo ($reservation['duree'] == 50) ? 'selected' : ''; ?>>50 minutes</option>
                                        <option value="1.67" <?php echo ($reservation['duree'] == 100) ? 'selected' : ''; ?>>1 heure 40</option>
                                        <option value="2.5" <?php echo ($reservation['duree'] == 150) ? 'selected' : ''; ?>>2 heures 30</option>
                                        <option value="3.33" <?php echo ($reservation['duree'] == 200) ? 'selected' : ''; ?>>3 heures 20</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-span-1 md:col-span-2 flex justify-end">
                            <a href="dashboard.php" class="bg-gray-500 text-white font-semibold py-2 px-4 rounded-md hover:bg-gray-600 transition-colors flex items-center mr-3">
                                <i class="fas fa-times mr-2"></i>
                                Annuler
                            </a>
                            <button type="submit" class="bg-primary text-white font-semibold py-2 px-4 rounded-md hover:bg-blue-700 transition-colors flex items-center">
                                <i class="fas fa-save mr-2"></i>
                                Enregistrer les modifications
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
</body>
</html>