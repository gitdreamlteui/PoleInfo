<?php
session_start();
if (!isset($_SESSION['token']) || $_SESSION['type_compte'] != 1) {
    header("Location: http://192.168.8.152/interface_login.php?error=expired");
    exit;
}
$token = $_SESSION['token'];
$api_url_user = "http://192.168.8.152:8000/utilisateurs/";
$api_url_matiere = "http://192.168.8.152:8000/matieres/";
$api_url_salle = "http://192.168.8.152:8000/salles/";
$api_url_creneau ="http://192.168.8.152:8000/creneaux/";
$api_url_classe ="http://192.168.8.152:8000/classes/";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $action = $_POST["action"] ?? '';

    if ($action == "ajouter_utilisateur") {
        ajouterUtilisateur($_POST);
    }
    else if ($action == "ajouter_matiere") {
        ajouterMatiere($_POST);
    }
    else if ($action == "ajouter_creneau") {
        ajouterCreneau($_POST);
    }
    else if ($action == "ajouter_classe") {
        ajouterClasse($_POST);
    }
    else if ($action == "supprimer_utilisateur"){
        supprimerUtilisateur($_POST);
    }
    else if ($action == "supprimer_matiere"){
        supprimerMatiere($_POST);
    }
    else if ($action == "supprimer_salle"){
        supprimerSalle($_POST);
    }
    else if ($action == "supprimer_creneau"){
        supprimerCreneau($_POST);
    }
    else if ($action == "supprimer_classe"){
        supprimerClasse($_POST);
    }
}

function verif_HTTP($code){
    if ($code === 201 || $code === 200) {
        $response_data = json_decode($response, true);
        $message = $response_data['message'] ?? "Utilisateur ajouté avec succès!";
        
        $_SESSION['info_message'] = $message;
        header("Location: interface_admin.php");
        exit;
    } else {
        $message = "Erreur lors de l'ajout de l'utilisateur : ";
        if (!empty($response)) {
            $error_data = json_decode($response, true);
            $message .= isset($error_data['detail']) ? $error_data['detail'] : (isset($error_data['message']) ? $error_data['message'] : 'Code ' . $http_code);
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
    $type = htmlspecialchars($data['type']);
    $password = htmlspecialchars($data['password']);
    $login = mb_strtolower(mb_substr($prenom, 0, 1) . $nom, 'UTF-8');
    
    $user = [    
        "login" => $login,
        "type" => intval($type),
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
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $token,
        "Content-Type: application/json",
    ]);
    
    // Exécuter la requête
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    verif_HTTP($http_code);
        header("Location: interface_admin.php");
        exit;
    }

function ajouterMatiere($data){
    global $api_url_matiere, $token;

    $nom = htmlspecialchars($data['add_matiere']);
    
    $matiere = [    
        "nom" => $nom
    ];
    
    // Convertir les données en JSON
    $jsonData = json_encode($matiere);
    
    // Initialiser cURL
    $ch = curl_init($api_url_matiere);
    
    // Configuration de la requête cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $token,
        "Content-Type: application/json",
    ]);
    
    // Exécuter la requête
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    verif_HTTP($http_code);
        header("Location: interface_admin.php");
        exit;
}

function ajouterCreneau($data){
    global $api_url_creneau, $token;

    $heure_debut = htmlspecialchars($data['add_creneau']);
    $time = explode(':', $heure_debut);
    $intervalSpec = sprintf('PT%dH%dM', $time[0], $time[1]);
    $creneau = [    
        "heure_debut" => $intervalSpec
    ];
    
    // Convertir les données en JSON
    $jsonData = json_encode($creneau);
    
    // Initialiser cURL
    $ch = curl_init($api_url_creneau);
    
    // Configuration de la requête cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $token,
        "Content-Type: application/json",
    ]);
    
    // Exécuter la requête
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    verif_HTTP($http_code);
        header("Location: interface_admin.php");
        exit;
}

function ajouterClasse($data){
    global $api_url_classe, $token;

    $nom = htmlspecialchars($data['add_classe']);
    
    $classe = [    
        "nom" => $nom
    ];
    
    // Convertir les données en JSON
    $jsonData = json_encode($classe);
    
    // Initialiser cURL
    $ch = curl_init($api_url_classe);
    
    // Configuration de la requête cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $token,
        "Content-Type: application/json",
    ]);
    
    // Exécuter la requête
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    verif_HTTP($http_code);
        header("Location: interface_admin.php");
        exit;
}
function supprimerUtilisateur($data){
    global $api_url_user, $token;

    $login = htmlspecialchars($data['sup_user']);
    $user=[
        "login"=>$login
    ];

       // Convertir les données en JSON
       $jsonData = json_encode($user);
    
       // Initialiser cURL
       $ch = curl_init($api_url_user);
       
       // Configuration de la requête cURL
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
       curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
       curl_setopt($ch, CURLOPT_HTTPHEADER, [
           "Authorization: Bearer " . $token,
           "Content-Type: application/json",
       ]);
       
       // Exécuter la requête
       $response = curl_exec($ch);
       $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
       $curl_error = curl_error($ch);
       curl_close($ch);
   
       verif_HTTP($http_code);
           header("Location: interface_admin.php");
           exit;
}

function supprimerClasse($data){
    global $api_url_classe, $token;
    $nom = htmlspecialchars($data['sup_classe']);
    $classe=[
        "nom"=>$nom
    ];

       // Convertir les données en JSON
       $jsonData = json_encode($classe);
    
       // Initialiser cURL
       $ch = curl_init($api_url_classe);
       
       // Configuration de la requête cURL
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
       curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
       curl_setopt($ch, CURLOPT_HTTPHEADER, [
           "Authorization: Bearer " . $token,
           "Content-Type: application/json",
       ]);
       
       // Exécuter la requête
       $response = curl_exec($ch);
       $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
       $curl_error = curl_error($ch);
       curl_close($ch);
   
       verif_HTTP($http_code);
           header("Location: interface_admin.php");
           exit;
}

function supprimerMatiere($data){
    
    global $api_url_matiere, $token;

    $nom = htmlspecialchars($data['sup_matiere']);
    $matiere=[
        "nom"=>$nom
    ];

       // Convertir les données en JSON
       $jsonData = json_encode($matiere);
    
       // Initialiser cURL
       $ch = curl_init($api_url_matiere);
       
       // Configuration de la requête cURL
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
       curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
       curl_setopt($ch, CURLOPT_HTTPHEADER, [
           "Authorization: Bearer " . $token,
           "Content-Type: application/json",
       ]);
       
       // Exécuter la requête
       $response = curl_exec($ch);
       $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
       $curl_error = curl_error($ch);
       curl_close($ch);
   
       verif_HTTP($http_code);
           header("Location: interface_admin.php");
           exit;
       }


function supprimerSalle($data){
    global $api_url_salle, $token;

    $numero = htmlspecialchars($data['sup_salle']);
    $salle=[
        "numero"=>$numero
    ];

       // Convertir les données en JSON
       $jsonData = json_encode($salle);
    
       // Initialiser cURL
       $ch = curl_init($api_url_salle);
       
       // Configuration de la requête cURL
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
       curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
       curl_setopt($ch, CURLOPT_HTTPHEADER, [
           "Authorization: Bearer " . $token,
           "Content-Type: application/json",
       ]);
       
       // Exécuter la requête
       $response = curl_exec($ch);
       $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
       $curl_error = curl_error($ch);
       curl_close($ch);
       verif_HTTP($http_code);
           header("Location: interface_admin.php");
           exit;
       }


function supprimerCreneau($data){
    global $api_url_creneau, $token;
    $heure_debut = htmlspecialchars($data['sup_creneau']);
    $time = explode(':', $heure_debut);
    $intervalSpec = sprintf('PT%dH%dM', $time[0], $time[1]);
    $creneau=[
        "heure_debut"=>$intervalSpec
    ];
       // Convertir les données en JSON
       $jsonData = json_encode($creneau);
    
       // Initialiser cURL
       $ch = curl_init($api_url_creneau);
       
       // Configuration de la requête cURL
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
       curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
       curl_setopt($ch, CURLOPT_HTTPHEADER, [
           "Authorization: Bearer " . $token,
           "Content-Type: application/json",
       ]);
       
       // Exécuter la requête
       $response = curl_exec($ch);
       $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
       $curl_error = curl_error($ch);
       curl_close($ch);
   
       verif_HTTP($http_code);
        header("Location: interface_admin.php");
        exit;
       }
?>
