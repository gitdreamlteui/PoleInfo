<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $api_url_token = "http://127.0.0.1:8000/token";

    // Utilisation de cURL pour une meilleure gestion des erreurs
    $ch = curl_init($api_url_token);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'password',
        'username' => $username,
        'password' => $password
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);

    $token_response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200) {
        $token_data = json_decode($token_response, true);
        
        if (isset($token_data['access_token'])) {
            $_SESSION['token'] = $token_data['access_token'];
            $_SESSION['username'] = $username;
            // Redirection après le stockage du token
            header('Location: http://192.168.8.152/user/dashboard.php');
            exit();
        } else {
            // Si le token n'est pas présent dans la réponse
            header('Location: http://192.168.8.152/user/index.php?error=no_token');
            exit();
        }
    } else {
        // Gestion des erreurs HTTP (400, 401, etc.)
        $error_message = "Erreur lors de l'authentification";
        if ($http_code === 400) {
            $error_message = "Identifiants incorrects";
        } elseif ($http_code === 401) {
            $error_message = "Non autorisé";
        }
        header('Location: http://192.168.8.152/user/index.php?error=' . urlencode($error_message));
        exit();
    }
}
?>