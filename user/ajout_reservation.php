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

$token = $_SESSION['token'];
$api_url = getApiUrl('/reservations/');

$numero_salle = $_POST['salle'] ?? '';
$nom_matiere = $_POST['matiere'] ?? '';
$classes = isset($_POST['classe']) && is_array($_POST['classe']) ? $_POST['classe'] : [];
$date = $_POST['date_reserv'] ?? '';
$info = $_POST['message'] ?? '';
$heure_debut = $_POST['startTime'] ?? '';
$duree = number_format(floatval($_POST['duration'] ?? 0), 3, '.', '');

$nom_classe = !empty($classes) ? implode(", ", $classes) : "";

if (empty($numero_salle) || empty($nom_matiere) || empty($nom_classe) || empty($date) || empty($heure_debut) || $duree <= 0) {
    die("Erreur : Tous les champs obligatoires doivent être remplis.");
}
$login_user = $_SESSION['username'] ?? "Inconnu";

// Préparer les données pour l'API
$data = [
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
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($http_code === 201 || $http_code === 200) {
    $response_data = json_decode($response, true);
    $message = $response_data['message'] ?? "Réservation ajoutée avec succès!";
    
    $_SESSION['info_message'] = $message;
    header("Location: " . getWebUrl('user/dashboard.php'));
    exit;
} else {
    $error_message = "";
    
    if ($response) {
        $response_data = json_decode($response, true);
        
        if (is_array($response_data)) {
            // Recherche dans différents champs possibles de message d'erreur
            if (isset($response_data['detail'])) {
                // Si le message d'erreur est sous forme de string direct
                if (is_string($response_data['detail'])) {
                    $error_message = $response_data['detail'];
                } 
                // Si le message d'erreur est un tableau ou un objet
                else {
                    $error_message = json_encode($response_data['detail']);
                }
            } elseif (isset($response_data['message'])) {
                $error_message = $response_data['message'];
            } elseif (isset($response_data['error'])) {
                $error_message = $response_data['error'];
            }
        }
    }
    
    // Si aucun message d'erreur n'a pu être extrait, utiliser un message générique
    if (empty($error_message)) {
        $error_message = "Erreur lors de l'ajout de la réservation";
        if (!empty($curl_error)) {
            $error_message .= ": " . $curl_error;
        } else {
            $error_message .= " (code " . $http_code . ")";
        }
    }
    
    // Enrichir le message d'erreur en fonction du code HTTP
    if ($http_code === 400) {
        // Vérifier si le message d'erreur concerne déjà une réservation existante
        if (strpos($error_message, "déjà réservé") === false && 
            strpos($error_message, "réserv") === false) {
            // Si le message ne contient pas déjà cette information
            if (strpos($error_message, "Cette salle est déjà réservé pour cet horaire") === false) {
                // Le message original de l'API n'a pas été récupéré correctement
                $error_message = "Cette salle est déjà réservée pour cet horaire";
            }
        }
    } elseif ($http_code === 404) {
        if (empty($error_message)) {
            $error_message = "Utilisateur non trouvé ou problème avec les données de réservation";
        }
    } elseif ($http_code === 401 || $http_code === 403) {
        if (empty($error_message)) {
            $error_message = "Vous n'avez pas les droits nécessaires pour effectuer cette action";
        }
    }
    
    $_SESSION['info_message'] = $error_message;
    header("Location: " . getWebUrl('user/dashboard.php'));
    exit;
}
?>
