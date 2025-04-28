<?php
require_once __DIR__ . '/../../config.php';

function getCreneaux() {
    $creneaux = [];
    
    $get_creneau = getApiUrl('/creneaux/');
    $reponse_creneau = file_get_contents($get_creneau);
    $data_creneau = json_decode($reponse_creneau, true);
    
    if (is_array($data_creneau)) {
        foreach ($data_creneau as $item) {
            $interval = new DateInterval($item['heure_debut']);
            $heures = $interval->h;
            $minutes = $interval->i;
            $creneau = sprintf("%02d:%02d", $heures, $minutes);
            $creneaux[] = $creneau;
        }
    }
    return $creneaux;
}
?>
