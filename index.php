<?php
$request_reservation = "http://192.168.8.152:8000/reservations/?croissant=true"; // Remplace par l'URL de l'API
$response_reservation = file_get_contents($request_reservation);
$data=json_decode($response_reservation, true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - Système d'information BTS</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 800px;
            margin: 60px auto 20px auto;
            padding: 10px;
        }
        .header {
            background-color: #003366;
            color: white;
            padding: 10px;
            position: fixed;
            top: 0;
            width: 100%;
            height: 40px;
            z-index: 1000;
        }
        .header-content {
            width: 800px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .login-btn {
            background-color: #f0f0f0;
            border: 1px solid #003366;
            color: #003366;
            padding: 5px 10px;
            cursor: pointer;
            font-weight: bold;
        }
        .title-box {
            background-color: #003366;
            color: white;
            padding: 8px;
            margin-bottom: 15px;
            font-weight: bold;
            font-size: 16px;
            border: 1px solid #001a33;
        }
        .reservation-item {
            margin-bottom: 10px;
            border: 1px solid #ccc;
            cursor: pointer;
        }
        .reservation-blue {
            background-color: #e6eeff;
            padding: 8px;
            border-bottom: 1px solid #ccc;
        }
        .reservation-dark {
            background-color: #003366;
            color: white;
            padding: 8px;
            border-bottom: 1px solid #001a33;
        }
        .details {
            background-color: #f9f9f9;
            padding: 10px;
            border-top: 1px dotted #ccc;
            display: none;
        }
        .info-right {
            text-align: right;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-top: 20px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
    </style>
    <script>
        function toggleDetails(id) {
            var details = document.getElementById(id);
            if (details.style.display === "none" || details.style.display === "") {
                details.style.display = "block";
            } else {
                details.style.display = "none";
            }
        }
    </script>
</head>
<body>
    <!-- Barre de navigation -->
    <div class="header">
        <div class="header-content">
            <div><strong>Système d'information BTS - Réservation</strong></div>
            <a href="interface_login.php">
                <button class="login-btn">Se connecter</button>
            </a>
        </div>
    </div>
    
    <div class="container">
        <!-- Titre -->
        <div class="title-box">
            Tableau Prévisionnel des séances à venir
        </div> 

<?php
$compteur=0;
foreach($data as $data){
    $compteur=$compteur+1;
    $matiere=$data['nom_matiere'];
    $salle=$data['numero_salle'];
    $date=$data['date'];
    $info=$data['info'];
    $classe=$data['noms_classes'];
    $prenom=$data['prenom'];
    $nom=$data['nom_user'];
    $debut=$data['heure_debut'];
    $duree=$data['duree'];
    //traitement de l'heure debut
    $interval = new DateInterval($debut);
    $heures = $interval->h;
    $minutes = $interval->i;
    $heureString = sprintf("%02d:%02d", $heures, $minutes);
    $heureFloat = $heures + ($minutes / 60);
    //traitemement heure fin
    $heuresfin = floor($heureFloat+$duree);
    $minutesfin = ($heureFloat+$duree-$heuresfin)*60;  // Partie décimale convertie en minutes
    $heurefinString = sprintf("%02d:%02d", $heuresfin, $minutesfin);
    //traitement de la date
    $dt = new DateTime($date);
    $date = $dt->format("j/m");
    //
    $detailsID="details_$compteur";
    //affichage
    $itemClass = ($compteur % 2 == 1) ? "reservation-blue" : "reservation-dark";
    
    echo <<<HTML
    <div class="reservation-item">
        <div class="$itemClass" onclick="toggleDetails('$detailsID')">
            <div><strong>$matiere | $classe | $salle</strong></div>
            <div class="info-right"><strong>$heureString - $heurefinString</strong></div>
            <div class="info-right"><strong>$date</strong></div>
            <div><strong>$prenom $nom</strong></div>
        </div>
        <div id="$detailsID" class="details">
            <p>$info</p>
        </div>
    </div>
    HTML;
}
?>      
        <div class="footer">
            © 2025 Système d'information BTS - Tous droits réservés
        </div>
    </div>
</body>
</html>
