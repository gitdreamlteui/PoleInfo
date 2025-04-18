<?php


$reservation_id = intval($_GET['id']);

// Préparer les données pour la requête DELETE
$data = json_encode(['id_reservation' => $reservation_id]);

// Configuration de la requête cURL
$api_url = "http://192.168.8.152:8000/reservations/";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data),
    'Authorization: Bearer ' . $_SESSION['token']
]);

// Exécuter la requête
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Traiter la réponse
$result = json_decode($response, true);

if ($http_code == 200) {
    $_SESSION['success_message'] = "La réservation a été supprimée avec succès";
} else {
    $_SESSION['error_message'] = $result['detail'] ?? "Erreur lors de la suppression de la réservation";
}

// Rediriger vers le tableau de bord
header("Location: dashboard.php");
exit;
?>
