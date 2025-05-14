<?php
require_once __DIR__ . '/../config.php';
session_start();

if (!isset($_SESSION['token'])) {
    header("Location: " . getWebUrl('login.php?error=unauthorized'));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Méthode non autorisée.");
}

// Récupération de l'ID de la réservation
if (!isset($_POST['id_reservation']) || empty($_POST['id_reservation'])) {
    die("Erreur : ID de réservation manquant.");
}

$id_reservation = intval($_POST['id_reservation']);
$token = $_SESSION['token'];
$api_url = getApiUrl('/reservations/');

$numero_salle = $_POST['salle'] ?? '';
$nom_matiere = $_POST['matiere'] ?? '';
$classes = isset($_POST['classe']) && is_array($_POST['classe']) ? $_POST['classe'] : [];
$date = $_POST['date_reserv'] ?? '';
$info = $_POST['message'] ?? '';
$heure_debut = $_POST['startTime'] ?? '';
$duree = $_POST['duration'];

$nom_classe = !empty($classes) ? implode(", ", $classes) : "";

if (empty($numero_salle) || empty($nom_matiere) || empty($nom_classe) || empty($date) || empty($heure_debut) || $duree <= 0) {
    die("Erreur : Tous les champs obligatoires doivent être remplis.");
}

$login_user = $_SESSION['login'] ?? $_SESSION['username'] ?? "Inconnu";

// Préparer les données pour l'API
$data = [
    "id_reservation" => $id_reservation,
    "duree" => $duree,
    "date" => $date,
    "info" => $info,
    "numero_salle" => $numero_salle,
    "nom_matiere" => $nom_matiere,
    "heure_debut_creneau" => $heure_debut,
    "login_user" => $login_user,
    "nom_classe" => $nom_classe,
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($http_code === 200) {
    $response_data = json_decode($response, true);
    $message = $response_data['message'] ?? "Réservation modifiée avec succès!";
    
    $_SESSION['info_message'] = $message;
    header("Location: " . getWebUrl('user/dashboard.php'));
    exit;
} else {
    $error_message = "";
    
    if ($response) {
        $response_data = json_decode($response, true);
        
        if (is_array($response_data)) {
            if (isset($response_data['detail'])) {
                if (is_string($response_data['detail'])) {
                    $error_message = $response_data['detail'];
                } else {
                    $error_message = json_encode($response_data['detail']);
                }
            } elseif (isset($response_data['message'])) {
                $error_message = $response_data['message'];
            } elseif (isset($response_data['error'])) {
                $error_message = $response_data['error'];
            }
        }
    }
    
    if (empty($error_message)) {
        $error_message = "Erreur lors de la modification de la réservation";
        if (!empty($curl_error)) {
            $error_message .= ": " . $curl_error;
        } else {
            $error_message .= " (code " . $http_code . ")";
        }
    }
    
    // Enrichir le message d'erreur en fonction du code HTTP
    if ($http_code === 400) {
        if (strpos($error_message, "déjà réservé") === false && 
            strpos($error_message, "réserv") === false) {
            if (strpos($error_message, "Cette salle est déjà réservé pour cet horaire") === false) {
                $error_message = "Cette salle est déjà réservée pour cet horaire";
            }
        }
    } elseif ($http_code === 404) {
        if (empty($error_message)) {
            $error_message = "Réservation non trouvée ou problème avec les données";
        }
    } elseif ($http_code === 401 || $http_code === 403) {
        if (empty($error_message)) {
            $error_message = "Vous n'avez pas les droits nécessaires pour effectuer cette action";
        }
    }
    
    $_SESSION['error_message'] = $error_message;
    header("Location: " . getWebUrl('user/dashboard.php'));
    exit;
}
?>
