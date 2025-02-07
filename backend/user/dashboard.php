<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: login.php");
    exit;
}

$token = $_SESSION['token'];
$api_url_verify = "http://127.0.0.1:8000/verify-token/";

// Configuration et exécution de la requête cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url_verify);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
]);

// Exécution de la requête une seule fois
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// Vérification du code HTTP
if ($http_code != 200) {
    session_destroy();
    header("Location: login.php?error=expired");
    exit;
}

// Décodage JSON une seule fois
$data = json_decode($response, true);
if (!$data) {
    die("Erreur : Impossible de décoder le JSON.");
}

// Récupération du nom d'utilisateur avec gestion d'erreur
$username = $data["user"] ?? "Inconnu";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord</title>
</head>
<body>
    <h2>Bienvenue <?php echo htmlspecialchars($username); ?> sur votre tableau de bord</h2>
    <p>Votre token est valide.</p>
    <a href="logout.php">Se déconnecter</a>
</body>
</html>