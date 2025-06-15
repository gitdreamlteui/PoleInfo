<?php
// RECUPERER_SALLES.PHP

require_once __DIR__ . '/../../config.php';

function getSalles() {
    $salles = [];
    
    $get_salles = getApiUrl('/salles/');
    $reponse_salles = file_get_contents($get_salles);
    $data_salles = json_decode($reponse_salles, true);
    
    if (is_array($data_salles)) {
        foreach ($data_salles as $item) {
            $salle = [
                'id' => $item['id_salle'],
                'numero' => $item['numero'],
                'capacite' => $item['capacite'],
                'type' => $item['type']
            ];
            $salles[] = $salle;
        }
    }
    return $salles;
}
?>
