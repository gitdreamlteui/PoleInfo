<?php
require_once __DIR__ . '/../config.php';
session_start();
if (!isset($_SESSION['token']) || $_SESSION['type_compte'] != 1) {
    header('Location: ' . getWebUrl("/interface_login.php?error=expired"));
    exit;
}
// Configuration de la base de données - REMPLACEZ PAR VOS VALEURS RÉELLES
$host = 'localhost'; // ou l'adresse de votre serveur MySQL
$db = 'poleinfo'; // nom de votre base de données
$user = 'root'; // votre nom d'utilisateur MySQL
$pass = 'cielPOLEINFO25**'; // votre mot de passe MySQL

// Fonction pour restaurer la base de données
function restoreDatabase($host, $user, $pass, $db, $filePath) {
    try {
        // Vérifier que le fichier existe
        if (!file_exists($filePath)) {
            throw new Exception("Le fichier de sauvegarde n'existe pas: $filePath");
        }

        // Vérifier que le fichier n'est pas vide
        if (filesize($filePath) === 0) {
            throw new Exception("Le fichier de sauvegarde est vide");
        }

        // Méthode 1: Utiliser la commande mysql en ligne de commande
        $command = sprintf(
            'mysql -h%s -u%s -p%s %s < %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($user),
            escapeshellarg($pass),
            escapeshellarg($db),
            escapeshellarg($filePath)
        );

        // Exécuter la commande
        exec($command, $output, $returnVar);

        // Vérifier si la commande a réussi
        if ($returnVar !== 0) {
            $error = "Erreur lors de l'importation via ligne de commande: " . implode("\n", $output);
            error_log($error);
            return [
                'success' => false,
                'message' => $error
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Base de données restaurée avec succès'
        ];
    } catch (Exception $e) {
        error_log("Erreur de restauration: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

// Traitement de la requête
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier si un fichier a été envoyé
    if (isset($_FILES['sql_file']) && $_FILES['sql_file']['error'] === UPLOAD_ERR_OK) {
        // Vérifier l'extension du fichier
        $fileInfo = pathinfo($_FILES['sql_file']['name']);
        $extension = strtolower($fileInfo['extension'] ?? '');
        
        if ($extension !== 'sql') {
            $_SESSION['restore_error'] = 'Seuls les fichiers SQL sont acceptés.';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
        
        // Déplacer le fichier vers un emplacement temporaire
        $tempFile = sys_get_temp_dir() . '/' . uniqid('restore_') . '.sql';
        
        if (move_uploaded_file($_FILES['sql_file']['tmp_name'], $tempFile)) {
            // Restaurer la base de données
            $result = restoreDatabase($host, $user, $pass, $db, $tempFile);
            
            // Supprimer le fichier temporaire
            @unlink($tempFile);
            
            if ($result['success']) {
                $_SESSION['restore_success'] = $result['message'];
            } else {
                $_SESSION['restore_error'] = $result['message'];
            }
        } else {
            $_SESSION['restore_error'] = 'Échec du téléchargement du fichier.';
        }
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $_SESSION['restore_error'] = 'Aucun fichier n\'a été envoyé ou une erreur s\'est produite.';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>