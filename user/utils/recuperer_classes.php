<?php
function getClasses() {
    $classes = [];
    
    $get_classe = "http://192.168.8.152:8000/classes/";
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
