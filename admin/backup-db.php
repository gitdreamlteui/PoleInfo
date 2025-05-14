<?php
require_once __DIR__ . '/../config.php';

use Dotenv\Dotenv;

session_start();
if (!isset($_SESSION['token']) || $_SESSION['type_compte'] != 1) {
    header('Location' . getWebUrl("/interface_login.php?error=expired"));
    exit;
}

// Charger les variables d’environnement
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST'];
$db   = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASSWORD'];
$backupDir = $_ENV['BACKUP_DIR'];

/// Créer le dossier de sauvegarde s'il n'existe pas
if (!is_dir($backupDir)) {
    if (!mkdir($backupDir, 0755, true)) {
        throw new Exception("Impossible de créer le dossier de sauvegarde");
    }
}
try {
// Nom du fichier de sauvegarde
$filename = $dbname . '_sauvegarde_' . date('Y-m-d_H-i-s') . '.sql';
$backupFile = $backupDir . '/' . $filename;
// Commande mysqldump
$command = sprintf(
    'mysqldump --opt --skip-comments -h%s -u%s -p%s %s > %s',
    escapeshellarg($host),
    escapeshellarg($username),
    escapeshellarg($password),
    escapeshellarg($dbname),
    escapeshellarg($backupFile)
);

exec($command, $commandOutput, $returnVar);

if ($returnVar !== 0) {
    throw new Exception("La commande mysqldump a échoué avec le code $returnVar");
}

// Pour la méthode PDO: sauvegarder dans un fichier
file_put_contents($backupFile, $output);

// Vérifier que le fichier existe
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

// Envoyer le fichier au navigateur
readfile($backupFile);

// Nettoyer
unlink($backupFile);
header('Location: ' . getWebUrl('/interface_admin.php'));
exit;
exit;
}
catch (Exception $e) {
// Gérer les erreurs de façon sécurisée
error_log("Erreur de sauvegarde de BDD: " . $e->getMessage());

// Rediriger vers une page d'erreur ou afficher un message
$_SESSION['backup_error'] = "La sauvegarde a échoué. Veuillez contacter l'administrateur.";
header('Location: ' . getWebUrl('/interface_admin.php?error=backup_failed'));
exit;
}
?>