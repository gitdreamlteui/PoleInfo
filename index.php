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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            width: 900px;
            margin: 80px auto 30px auto;
            padding: 15px;
        }
        .header {
            background-color: #1a4d85;
            color: white;
            padding: 15px;
            position: fixed;
            top: 0;
            width: 100%;
            height: 40px;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .header-content {
            width: 900px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .login-btn {
            background-color: #ffffff;
            border: none;
            border-radius: 5px;
            color: #1a4d85;
            padding: 8px 15px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        .login-btn:hover {
            background-color: #f0f5ff;
        }
        .title-box {
            background-color: #1a4d85;
            color: white;
            padding: 12px 15px;
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 16px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .reservation-item {
            margin-bottom: 15px;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: transform 0.1s;
        }
        .reservation-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 5px rgba(0,0,0,0.15);
        }
        .reservation-blue {
            background-color: #e6f0ff;
            padding: 12px 15px;
            border-bottom: 1px solid #d5e4ff;
            cursor: pointer;
        }
        .reservation-dark {
            background-color: #1a4d85;
            color: white;
            padding: 12px 15px;
            cursor: pointer;
        }
        .details {
            background-color: #f9fbff;
            padding: 15px;
            border-top: 1px solid #eaeef5;
            display: none;
            line-height: 1.5;
        }
        .info-right {
            text-align: right;
            margin-top: 3px;
            font-size: 0.95em;
        }
        .footer {
            text-align: center;
            font-size: 13px;
            color: #7a8999;
            margin-top: 30px;
            border-top: 1px solid #e5e9ef;
            padding-top: 15px;
        }
        .reservation-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        .text-sm {
            font-size: 0.9em;
            opacity: 0.9;
            margin-top: 5px;
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
            <div style="font-weight: 600;">Système d'information BTS - Réservation</div>
            <a href="interface_login.php" style="text-decoration: none;">
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
            <div class="reservation-info">
                <div><strong>$matiere | $classe | $salle</strong></div>
                <div><strong>$heureString - $heurefinString</strong></div>
            </div>
            <div class="info-right"><strong>$date</strong></div>
            <div class="text-sm"><strong>$prenom $nom</strong></div>
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
