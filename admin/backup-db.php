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
$backupDir = '/var/www/html/PoleInfo/backups'; // chemin absolu vers le dossier de sauvegarde

try {

    // Vérifier que le dossier est accessible en écriture
    if (!is_writable($backupDir)) {
        throw new Exception("Le dossier de sauvegarde n'est pas accessible en écriture");
    }
    
    // Nom du fichier de sauvegarde
    $filename = $db . '_sauvegarde_' . date('Y-m-d_H-i-s') . '.sql';
    $backupFile = $backupDir . '/' . $filename;
    
    // Commande mysqldump
    $command = sprintf(
        'mysqldump --opt --skip-comments -h%s -u%s -p%s %s > %s',
        escapeshellarg($host),
        escapeshellarg($user),
        escapeshellarg($pass),
        escapeshellarg($db),
        escapeshellarg($backupFile)
    );

    // Exécuter la commande
    exec($command, $commandOutput, $returnVar);

    if ($returnVar !== 0) {
        throw new Exception("La commande mysqldump a échoué avec le code $returnVar");
    }

    // Vérifier que le fichier existe et n'est pas vide
    if (!file_exists($backupFile) || filesize($backupFile) == 0) {
        throw new Exception("La sauvegarde n'a pas été créée correctement");
    }

    // Configurer les en-têtes pour le téléchargement
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($backupFile));

    // Vider les tampons de sortie
    ob_clean();
    flush();

    // Envoyer le fichier au navigateur
    readfile($backupFile);

    // Nettoyer
    //unlink($backupFile);
    exit;
}
catch (Exception $e) {
    // Gérer les erreurs de façon sécurisée
    error_log("Erreur de sauvegarde de BDD: " . $e->getMessage());

    // Rediriger vers une page d'erreur
    $_SESSION['backup_error'] = "La sauvegarde a échoué: " . $e->getMessage();
    header('Location: ' . getWebUrl('/admin/interface_admin.php?error=backup_failed'));
    exit;
}
?>