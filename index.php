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
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function toggleDetails(id) {
            const details = document.getElementById(id);
            if (details.style.height === "" || details.style.height === "0px") {
                details.style.height = details.scrollHeight + "px";
                details.classList.remove("opacity-0");
                details.classList.add("opacity-100");
            }   
            else {
                details.style.height = "0px";
                details.classList.remove("opacity-100");
                details.classList.add("opacity-0");
            }
        }
    </script>
</head>
<body class="bg-slate-50 p-10">
    
    <!-- Barre de navigation -->
    <header class="bg-indigo-600 text-white p-4 flex justify-between items-center w-full fixed top-0 left-0 right-0 shadow-md">
        <h1 class="text-xl font-bold">Système d'information BTS - Réservation</h1>
        <a href="interface_login.php">
            <button class="bg-white px-4 py-2 rounded-md text-indigo-600 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors shadow-md font-semibold">
                Se connecter
            </button>
        </a>
    </header>
    
    <div class="w-full px-4 mt-20">
        <!-- Titre -->
        <div class="bg-indigo-600 text-white text-xl font-bold p-4 rounded-lg mb-6 shadow-lg">
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
    if($compteur%2==1)
    {
        echo <<<HTML
        <div class="w-full space-y-4 mb-4">
            <div class="bg-white shadow-lg p-4 rounded-lg cursor-pointer hover:bg-indigo-50 transition-colors" onclick="toggleDetails('$detailsID')">
                <p><strong>$matiere | $classe | $salle</strong></p>
                <p class="flex justify-end"><strong>$heureString - $heurefinString</strong></p>
                <p class="flex justify-end"><strong>$date</strong></p>
                <p class="text-sm text-gray-800"><strong>$prenom - $nom</strong></p>
            </div>
            <div id="$detailsID" class="bg-gray-100 p-4 rounded-lg overflow-hidden transition-all duration-300 ease-in-out opacity-0 shadow-inner" style="height: 0px;">
                <p>$info</p>
            </div>
        </div>
        HTML;
    }
    elseif($compteur%2==0)
    {
        echo <<<HTML
        <div class="w-full space-y-4 mb-4">
            <div class="bg-orange-400 text-white p-4 rounded-lg cursor-pointer hover:bg-indigo-600 transition-colors shadow-lg" onclick="toggleDetails('$detailsID')">
                <p><strong>$matiere | $classe | $salle</strong></p>
                <p class="flex justify-end"><strong>$heureString - $heurefinString</strong></p>
                <p class="flex justify-end"><strong>$date</strong></p>
                <p class="text-sm"><strong>$prenom - $nom</strong></p>
            </div>
            <div id="$detailsID" class="bg-gray-100 p-4 rounded-lg overflow-hidden transition-all duration-300 ease-in-out opacity-0 shadow-inner" style="height: 0px;">
                <p>$info</p>
            </div>
        </div>
        HTML;
    }
}
?>      
    </div>
    
    <div class="mt-6 text-center text-sm text-gray-500">
        <p>© 2025 Système d'information BTS - Tous droits réservés</p>
    </div>
</body>
</html>
