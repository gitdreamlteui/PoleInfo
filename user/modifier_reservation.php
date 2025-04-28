<?php
// modifier_reservation.php
require_once __DIR__ . '/../config.php';
session_start();

if (!isset($_SESSION['token'])) {
    header("Location: " . getWebUrl('interface_login.php?error=expired'));
    exit;
}

$token = $_SESSION['token'];
$username = $_SESSION['username'];

// Vérifier l'ID de réservation
$id_reservation = isset($_POST['id_reservation']) ? intval($_POST['id_reservation']) : 0;
if ($id_reservation <= 0) {
    $_SESSION['info_message'] = "ID de réservation invalide.";
    header("Location: dashboard.php");
    exit;
}

// Récupérer et nettoyer les données du formulaire
$date_reserv = $_POST['date_reserv'] ?? '';
$startTime = $_POST['startTime'] ?? '';
$duration = isset($_POST['duration']) ? $_POST['duration'] : 0; // Garder la valeur originale sans conversion
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

// Transformer le tableau des classes en chaîne de caractères séparée par des virgules
$classes_string = implode(", ", $classe);

// Conversion de la durée en entier si nécessaire (multiplier par 100 pour conserver la précision)
// Cette conversion est basée sur l'hypothèse que l'API s'attend à un nombre de minutes entier
$duration_value = intval(floatval($duration) * 100); // Convertir en entier (en centièmes d'heure)

// Construction du payload selon la structure exacte attendue par l'API
$data = [
    "id_reservation" => $id_reservation,
    "date" => $date_reserv,
    "heure_debut_creneau" => $startTime,
    "duree" => $duration_value, // Utiliser la valeur entière
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

// Pour le débogage, vous pouvez décommenter ces lignes
// error_log("Données envoyées: " . json_encode($data));
// error_log("Réponse du serveur: " . $response);

// Traitement du retour
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