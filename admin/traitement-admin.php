<?php
session_start();
if (!isset($_SESSION['token']) and $_SESSION['type_compte']!=1) {
    header("Location: http://192.168.8.152/interface_login.php?error=expired");
    exit;
}
$token = $_SESSION['token'];
$api_url_user = "http://192.168.8.152:8000/utilisateurs/";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"] ?? '';

    if ($action == "ajouter_utilisateur") {
        ajouterUtilisateur($_POST);
    }
}

function ajouterUtilisateur($data) {

    $nom=htmlspecialchars($data['nom']);
    $prenom=htmlspecialchars($data['prenom']);
    $type=htmlspecialchars($data['type']);
    $password=htmlspecialchars($data['password']);
    $login=mb_strtolower(mb_substr($prenom, 0, 1) . $nom, 'UTF-8');
    
    $user = [    
        "login" => $login,
        "type" => $type,
        "nom" => $nom,
        "prenom" => $prenom,
        "password" => $password
    ];
    
    // Convertir les données en JSON
    $jsonData = json_encode($user);
    
    // Initialiser cURL
    $ch = curl_init($api_url_user);
    
    // Configuration de la requête cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json",
        "Content-Length: " . strlen($jsonData)
    ]);
    
    // Exécuter la requête
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
}
?>