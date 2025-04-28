<?php
require_once __DIR__ . '/../../config.php';

function getReservations($nomProf = null) {
    $reservations = [];
    
    $get_reservation = getApiUrl('/reservations/?croissant=true');
    
    if (!empty($nomProf)) {
        $get_reservation .= "&prof=" . urlencode($nomProf);
    }
    
    $reponse_reservation = file_get_contents($get_reservation);
    $data_reservation = json_decode($reponse_reservation, true);
    
    if (is_array($data_reservation)) {
        foreach ($data_reservation as $item) {
            $reservation = [
                'id_reservation' => $item['id_reservation'],
                'duree' => $item['duree'],
                'date' => $item['date'],
                'info' => $item['info'],
                'numero_salle' => $item['numero_salle'],
                'capacite_salle' => $item['capacite_salle'],
                'type_salle' => $item['type_salle'],
                'nom_matiere' => $item['nom_matiere'],
                'heure_debut' => $item['heure_debut'],
                'nom_user' => $item['nom_user'],
                'prenom' => $item['prenom'],
                'noms_classes' => $item['noms_classes']
            ];
            $reservations[] = $reservation;
        }
    }
    return $reservations;
}