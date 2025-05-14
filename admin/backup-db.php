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
$backupDir = $_ENV['BACKUP_DIR'] ?? 'backups';

// Créer le dossier de sauvegarde s’il n’existe pas
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Nom du fichier de sauvegarde avec horodatage
$date = date('Y-m-d_H-i-s');
$file = "{$backupDir}/{$db}_backup_{$date}.sql";

// Commande mysqldump
$cmd = sprintf(
    'mysqldump -h%s -u%s -p%s %s > %s',
    escapeshellarg($host),
    escapeshellarg($user),
    escapeshellarg($pass),
    escapeshellarg($db),
    escapeshellarg($file)
);

// Exécuter la commande
exec($cmd, $output, $resultCode);

// Vérifier le résultat
if ($resultCode === 0) {
    echo "✅ Sauvegarde réussie : $file\n";
    // Téléchargement du fichier
    header('Content-Type: application/sql');
    header("Content-Disposition: attachment; filename=\"$file\"");
    readfile("$file");
    unlink("$file");
} else {
    echo "❌ Erreur pendant la sauvegarde. Code : $resultCode\n";
}
header('Location: ' . getWebUrl('admin.php'));
exit;
