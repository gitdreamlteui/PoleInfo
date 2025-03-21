<?php
/**
 * Configuration du serveur PoleInfo
 */

// Configuration de base
$config = [
    // Informations du serveur
    'server' => [
        'ip' => $_SERVER['SERVER_ADDR'] ?? '192.168.8.152', // IP du serveur Apache
        'hostname' => gethostname(),                    // Nom d'hôte du serveur
    ],
    
    // Adresses base de donnée et API
    'external_servers' => [
        'database' => [
            'ip' => '192.168.8.152',
            'port' => 3306,
        ],
        'api' => [
            'ip' => '192.168.8.152',
            'port' => 8000,
        ],
    ],
    
    'check_server_status' => function($ip, $port = 80, $timeout = 3) {
        $fp = @fsockopen($ip, $port, $errno, $errstr, $timeout);
        $status = !!$fp;
        if($fp) fclose($fp);
        return $status;
    }
];

/**
 * Fonction pour récupérer une IP de serveur spécifique
 * @param string $server_name Nom du serveur dans la configuration
 * @return string|null Adresse IP ou null si non trouvée
 */
function getServerIP($server_name = null) {
    global $config;
    
    // Retourne l'IP du serveur courant si aucun nom spécifié
    if ($server_name === null) {
        return $config['server']['ip'];
    }
    
    // Vérifie si le serveur externe demandé existe dans la configuration
    if (isset($config['external_servers'][$server_name])) {
        return $config['external_servers'][$server_name]['ip'];
    }
    
    return null;
}

/**
 * Fonction pour vérifier l'état d'un serveur
 * @param string $server_name Nom du serveur
 * @return bool État de la connexion
 */
function isServerAvailable($server_name) {
    global $config;
    
    if (isset($config['external_servers'][$server_name])) {
        $server = $config['external_servers'][$server_name];
        return $config['check_server_status']($server['ip'], $server['port']);
    }
    
    return false;
}
