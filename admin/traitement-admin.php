<?php
require_once __DIR__ . '/../config.php';

session_start();
if (!isset($_SESSION['token']) || $_SESSION['type_compte'] != 1) {
    header('Location' . getWebUrl("/interface_login.php?error=expired"));
    exit;
}

$token = $_SESSION['token'];
$api_url_user = getApiUrl('/utilisateurs/');
$api_url_matiere = getApiUrl('/matieres/');
$api_url_salle = getApiUrl('/salles/');
$api_url_creneau = getApiUrl('/creneaux/');
$api_url_classe = getApiUrl('/classes/');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"] ?? '';

    switch ($action) {
        case "ajouter_utilisateur":
            ajouterUtilisateur($_POST);
            break;
        case "ajouter_matiere":
            ajouterMatiere($_POST);
            break;
        case "ajouter_creneau":
            ajouterCreneau($_POST);
            break;
        case "ajouter_classe":
            ajouterClasse($_POST);
            break;
        case "ajouter_salle":
            ajouterSalle($_POST);
            break;
        case "supprimer_utilisateur":
            supprimerUtilisateur($_POST);
            break;
        case "supprimer_matiere":
            supprimerMatiere($_POST);
            break;
        case "supprimer_salle":
            supprimerSalle($_POST);
            break;
        case "supprimer_creneau":
            supprimerCreneau($_POST);
            break;
        case "supprimer_classe":
            supprimerClasse($_POST);
            break;
    }
}

function envoyerRequeteAPI($url, $token, $donnees, $methode = "POST") {
    $jsonData = json_encode($donnees);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $methode);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $token,
        "Content-Type: application/json",
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    verif_HTTP($http_code, $response, $curl_error);
}

function verif_HTTP($code, $response, $curl_error) {
    if ($code === 201 || $code === 200) {
        $_SESSION['info_message'] = "Opération réalisée avec succès !";
    } else {
        $message = "Erreur de l'opération : ";
        if (!empty($response)) {
            $error_data = json_decode($response, true);
            $message .= $error_data['detail'] ?? $error_data['message'] ?? 'Code ' . $code;
        } elseif (!empty($curl_error)) {
            $message .= $curl_error;
        } else {
            $message .= 'Code ' . $code;
        }

        $_SESSION['info_message'] = $message;
    }
}

function ajouterUtilisateur($data) {
    global $api_url_user, $token;

    $nom = htmlspecialchars($data['nom']);
    $prenom = htmlspecialchars($data['prenom']);
    $type = intval(htmlspecialchars($data['type']));
    $password = htmlspecialchars($data['password']);
    $login = mb_strtolower(mb_substr($prenom, 0, 1) . $nom, 'UTF-8');

    $user = [
        "login" => $login,
        "type" => $type,
        "nom" => $nom,
        "prenom" => $prenom,
        "password" => $password
    ];

    envoyerRequeteAPI($api_url_user, $token, $user, "POST");
    header("Location: interface_admin.php");
    exit;
}

function ajouterMatiere($data) {
    global $api_url_matiere, $token;
    $matiere = ["nom" => htmlspecialchars($data['add_matiere'])];
    envoyerRequeteAPI($api_url_matiere, $token, $matiere, "POST");
    header("Location: interface_admin.php");
    exit;
}

function ajouterCreneau($data) {
    global $api_url_creneau, $token;
    $heure_debut = htmlspecialchars($data['add_creneau']);
    $time = explode(':', $heure_debut);
    $intervalSpec = sprintf('PT%dH%dM', $time[0], $time[1]);
    $creneau = ["heure_debut" => $intervalSpec];
    envoyerRequeteAPI($api_url_creneau, $token, $creneau, "POST");
    header("Location: interface_admin.php");
    exit;
}

function ajouterClasse($data) {
    global $api_url_classe, $token;
    $classe = ["nom" => htmlspecialchars($data['add_classe'])];
    envoyerRequeteAPI($api_url_classe, $token, $classe, "POST");
    header("Location: interface_admin.php");
    exit;
}

function ajouterSalle($data) {
    global $api_url_salle, $token;
    $salle = [
        "numero" => htmlspecialchars($data['add_salle1']),
        "type" => htmlspecialchars($data['add_salle2']),
        "capacite" => intval(htmlspecialchars($data['add_salle3']))
    ];
    envoyerRequeteAPI($api_url_salle, $token, $salle, "POST");
    header("Location: interface_admin.php");
    exit;
}

function supprimerUtilisateur($data) {
    global $api_url_user, $token;
    $user = ["login" => htmlspecialchars($data['sup_user'])];
    envoyerRequeteAPI($api_url_user, $token, $user, "DELETE");
    header("Location: interface_admin.php");
    exit;
}

function supprimerClasse($data) {
    global $api_url_classe, $token;
    $classe = ["nom" => htmlspecialchars($data['sup_classe'])];
    envoyerRequeteAPI($api_url_classe, $token, $classe, "DELETE");
    header("Location: interface_admin.php");
    exit;
}

function supprimerMatiere($data) {
    global $api_url_matiere, $token;
    $matiere = ["nom" => htmlspecialchars($data['sup_matiere'])];
    envoyerRequeteAPI($api_url_matiere, $token, $matiere, "DELETE");
    header("Location: interface_admin.php");
    exit;
}

function supprimerSalle($data) {
    global $api_url_salle, $token;
    $salle = ["numero" => htmlspecialchars($data['sup_salle'])];
    envoyerRequeteAPI($api_url_salle, $token, $salle, "DELETE");
    header("Location: interface_admin.php");
    exit;
}

function supprimerCreneau($data) {
    global $api_url_creneau, $token;
    $heure_debut = htmlspecialchars($data['sup_creneau']);
    $time = explode(':', $heure_debut);
    $intervalSpec = sprintf('PT%dH%dM', $time[0], $time[1]);
    $creneau = ["heure_debut" => $intervalSpec];
    envoyerRequeteAPI($api_url_creneau, $token, $creneau, "DELETE");
    header("Location: interface_admin.php");
    exit;
}
?>
