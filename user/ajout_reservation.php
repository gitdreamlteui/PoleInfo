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
$api_url = "http://127.0.0.1:8000/reservations/";

$salle = $_POST['salle'] ?? '';
$matiere = $_POST['matiere'] ?? '';
$classe = $_POST['classe'] ?? '';
$date_reserv = $_POST['date_reserv'] ?? '';
$message = $_POST['message'] ?? '';
$horaire_debut = $_POST['startTime'] ?? '';
$duration = $_POST['duration'] ?? '';

// Calcul de l'heure de fin
if (!empty($horaire_debut) && !empty($duration)) {
    $horaire_fin = date("H:i", strtotime($horaire_debut) + $duration * 60);
} else {
    die("Erreur : Heure de début et durée requises.");
}
$username = $data["user"] ?? "Inconnu";
$data = [
    "salle" => $salle,
    "matiere" => $matiere,
    "prof" => htmlspecialchars($username),
    "classe" => $classe,
    "horaire_debut" => $horaire_debut,
    "horaire_fin" => $horaire_fin,
    "date" => $date_reserv,
    "info" => $message
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

// Vérification de la réponse
if ($http_code === 201 || $http_code === 200) {
    $_SESSION['success_message'] = "Réservation ajoutée avec succès!";
    header("Location: /user/dashboard.php?success=reservation_added");
    exit;
} else {
    echo "<pre>";
    echo "Erreur lors de l'ajout de la réservation.\n";
    echo "Réponse API : " . htmlspecialchars($response) . "\n";
    echo "Code HTTP : $http_code\n";
    echo "Erreur cURL : $curl_error\n";
    echo "</pre>";
}
?>