<?php
// modifier_reservation.php
require_once __DIR__ . '/../config.php';

session_start();
if (!isset($_SESSION['token'])) {
    header("Location: " . getWebUrl('interface_login.php?error=expired'));
    exit;
}

$token = $_SESSION['token'];

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $_SESSION['info_message'] = "Méthode non autorisée.";
    header("Location: dashboard.php");
    exit;
}

// Récupération et validation des données du formulaire
$id_reservation = isset($_POST['id_reservation']) ? intval($_POST['id_reservation']) : 0;
if ($id_reservation <= 0) {
    $_SESSION['info_message'] = "ID de réservation invalide.";
    header("Location: dashboard.php");
    exit;
}

$date_reserv = $_POST['date_reserv'] ?? '';
$startTime = $_POST['startTime'] ?? '';
$duration = $_POST['duration'] ?? '';
$info = $_POST['message'] ?? '';
$matiere = $_POST['matiere'] ?? '';
$salle = $_POST['salle'] ?? '';
$classe = $_POST['classe'] ?? [];

// Validation
$errors = [];
if (empty($date_reserv)) {
    $errors[] = "La date de réservation est requise.";
}
if (empty($startTime)) {
    $errors[] = "L'heure de début est requise.";
}
if (empty($duration)) {
    $errors[] = "La durée est requise.";
}
if (empty($matiere)) {
    $errors[] = "La matière est requise.";
}
if (empty($salle)) {
    $errors[] = "La salle est requise.";
}
if (empty($classe)) {
    $errors[] = "Au moins une classe doit être sélectionnée.";
}

if (!empty($errors)) {
    $_SESSION['info_message'] = implode(" ", $errors);
    header("Location: edit.php?id=" . $id_reservation);
    exit;
}

$classes_string = implode(", ", $classe);

// CORRECTION: Utiliser directement la valeur décimale de la durée
// sans la multiplier par 100, pour conserver le format attendu par l'API
$duration_value = floatval($duration);

// Préparation des données à envoyer à l'API
$data = [
    "id_reservation" => $id_reservation,
    "date" => $date_reserv,
    "heure_debut_creneau" => $startTime,
    "duree" => $duration_value,
    "info" => $info,
    "numero_salle" => $salle,
    "nom_matiere" => $matiere,
    "nom_classe" => $classes_string
];

$api_url = getApiUrl("/reservations/");

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    "Authorization: Bearer $token"
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($http_code >= 200 && $http_code < 300) {
    $_SESSION['info_message'] = "Réservation modifiée avec succès.";
    header("Location: dashboard.php");
    exit;
} else {
    $error_message = "Erreur lors de la modification. Code HTTP: $http_code";
    if (!empty($curl_error)) {
        $error_message .= ". Erreur cURL: " . $curl_error;
    }
    if ($response) {
        $response_data = json_decode($response, true);
        if (is_array($response_data) && isset($response_data['detail'])) {
            $error_message .= ". Détail: " . json_encode($response_data['detail']);
        } else {
            $error_message .= ". Réponse: " . substr($response, 0, 200);
        }
    }
    
    $_SESSION['info_message'] = $error_message;
    header("Location: edit.php?id=" . $id_reservation);
    exit;
}
?>
