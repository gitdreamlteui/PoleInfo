<?php
session_start();
// Inclure le fichier de configuration
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Utiliser les fonctions de configuration pour récupérer l'URL
    $api_url_token = getTokenUrl();

    // Utilisation de cURL pour une meilleure gestion des erreurs
    $ch = curl_init($api_url_token);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => getGrantType(),
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
            $_SESSION['type_compte'] = $token_data['user_type'];
            $_SESSION['username'] = $token_data['user_name'];

            
            // Redirection après le stockage du token
            if ($_SESSION['type_compte'] == 1){
                header('Location: ' . getWebUrl('admin/interface_admin.php'));    
            }
            else {
                header('Location: ' . getWebUrl('user/dashboard.php'));
            }
            exit();
        } else {
            // Si le token n'est pas présent dans la réponse
            header('Location: ' . getWebUrl('interface_login.php?error=no_token'));
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
        header('Location: ' . getWebUrl('interface_login.php?error=' . urlencode($error_message)));
        exit();
    }
}
?>
