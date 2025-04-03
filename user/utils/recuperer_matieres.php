<?php
function getMatieres() {
    $matieres = [];
    
    $get_matieres = "http://192.168.8.152:8000/matieres/";
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
