<?php
/**
 * Configuration du serveur et des services
 */

// Configuration des serveurs
$config = [
    // Serveur d'API principal
    'api_server' => [
        'ip' => '192.168.8.152',
        'port' => '8000',
        'protocol' => 'https'
    ],
    
    // Serveur web
    'web_server' => [
        'ip' => '192.168.8.152',
        'port' => '80',
        'protocol' => 'http',
        'base_path' => '/'
    ],
    
    // Paramètres d'authentification
    'auth' => [
        'token_endpoint' => '/token',
        'grant_type' => 'password'
    ]
];

/**
 * Retourne l'URL complète du serveur API
 * @param string $endpoint Point de terminaison API (optionnel)
 * @return string URL complète
 */
function getApiUrl($endpoint = '') {
    global $config;
    $server = $config['api_server'];
    $base_url = "{$server['protocol']}://{$server['ip']}:{$server['port']}";
    return $base_url . $endpoint;
}

/**
 * Retourne l'URL complète du serveur Web
 * @param string $path Chemin relatif (optionnel)
 * @return string URL complète
 */
function getWebUrl($path = '') {
    global $config;
    $server = $config['web_server'];
    $base_url = "{$server['protocol']}://{$server['ip']}";
    
    if (($server['protocol'] === 'http' && $server['port'] !== '80') || 
        ($server['protocol'] === 'https' && $server['port'] !== '443')) {
        $base_url .= ":{$server['port']}";
    }
    
    $base_url .= $server['base_path'];
    return $base_url . $path;
}

/**
 * Retourne l'URL complète du point de terminaison de token
 * @return string URL du point de terminaison de token
 */
function getTokenUrl() {
    global $config;
    return getApiUrl($config['auth']['token_endpoint']);
}

/**
 * Retourne le type de grant pour l'authentification
 * @return string Type de grant
 */
function getGrantType() {
    global $config;
    return $config['auth']['grant_type'];
}
