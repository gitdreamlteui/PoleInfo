<?php
session_start();

if (!isset($_SESSION['token'])) {
    header("Location: /login.php?error=unauthorized");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Méthode non autorisée.");
}

$token = $_SESSION['token'];
$api_url = "http://192.168.8.152:8000/reservations/";

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
    header("Location: dashboard.php");
    exit;
} else {
    $message = "Erreur lors de l'ajout de la réservation: ";
    if (!empty($response)) {
        $error_data = json_decode($response, true);
        $message .= isset($error_data['message']) ? $error_data['message'] : 'Code ' . $http_code;
    } elseif (!empty($curl_error)) {
        $message .= $curl_error;
    } else {
        $message .= 'Code ' . $http_code;
    }
    
    $_SESSION['info_message'] = $message;
    header("Location: dashboard.php");
    exit;
}
?>
