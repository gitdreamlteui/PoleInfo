<?php
require_once __DIR__ . '/../../config.php';
function getClasses() {
    $classes = [];
    
    $get_classe = getApiUrl('/classes/');
    $reponse_classe = file_get_contents($get_classe);
    $data_classe = json_decode($reponse_classe, true);
    
    if (is_array($data_classe)) {
        foreach ($data_classe as $item) {
            $classe = [
                'nom' => $item['nom']
            ];
            $classes[] = $classe;
        }
    }
    return $classes;
}
?>
