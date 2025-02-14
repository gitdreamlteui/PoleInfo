<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $api_url_token = "http://127.0.0.1:8000/token";

    $token_response = file_get_contents($api_url_token, false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded",
            'content' => http_build_query([
                'username' => $username,
                'password' => $password
            ])
        ]
    ]));

    echo "Réponse de l'API : " . $token_response;

    $token_data = json_decode($token_response, true);
    
    if (isset($token_data['access_token'])) {
        $_SESSION['token'] = $token_data['access_token'];
        $_SESSION['username'] = $username;

        // Redirection après le stockage du token
        header('Location: http://poleinfo.local/user/dashboard.php');
        exit;
    } else {
        header('Location: http://poleinfo.local/user/index.html');
    }
}
?>
