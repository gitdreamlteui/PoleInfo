<?php
require_once __DIR__ . '/../config.php';
session_start();

$reservation_id = intval($_GET['id']);

$data = json_encode(['id_reservation' => $reservation_id]);

$api_url = getApiUrl('/reservations/');
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

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if ($http_code == 200) {
    $_SESSION['info_message'] = "La réservation a été supprimée avec succès";
} else {
    $_SESSION['error_message'] = $result['detail'] ?? "Erreur lors de la suppression de la réservation";
}

header("Location: " . getWebUrl('user/dashboard.php'));
exit;
?>
