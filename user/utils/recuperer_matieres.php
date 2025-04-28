<?php
require_once __DIR__ . '/../../config.php';

function getMatieres() {
    $matieres = [];
    
    $get_matieres = getApiUrl('/matieres/');
    $reponse_matieres = file_get_contents($get_matieres);
    $data_matiere = json_decode($reponse_matieres, true);
    
    if (is_array($data_matiere)) {
        foreach ($data_matiere as $item) {
            $matiere = [
                'nom' => $item['nom']
            ];
            $matieres[] = $matiere;
        }
    }
    return $matieres;
}
?>
