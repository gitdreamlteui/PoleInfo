<?php
$request_reservation = "http://192.168.8.152:8000/reservations"; // Remplace par l'URL de l'API
$response_reservation = file_get_contents($request_reservation);
$data=json_decode($response_reservation, true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - Systèmes d'informations de BTS</title>
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
<body class="p-10 bg-gray-100">
    
    <!-- Barre de navigation -->
    <header class="bg-blue-600 text-white p-4 flex justify-between items-center w-full fixed top-0 left-0 right-0">
        <h1 class="text-xl font-bold">Réservation</h1>
        <a href="interface_login.php">
            <button class="bg-white px-4 py-2 rounded-md text-black">Login</button>
        </a>
    </header>
    
    <div class="max-w-3xl mx-auto mt-16">
        <!-- Titre -->
        <div class="bg-blue-600 text-white text-xl font-bold p-4 rounded-md mb-4">
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
    $heuresfinFloat = $heureFloat+$duree;
    $heuresfin = floor($heuresfinFloat);
    $minutesfin = round(($heuresfin - $heuresFloat) / 60); // Partie décimale convertie en minutes
    $heurefinString = sprintf("%02d:%02d", $heuresfin, $minutesfin);
    //traitement de la date
    $dt = new DateTime($date);
    $date = $dt->format("j/m");
    //affichage
    echo <<<HTML
    <div class="space-y-4">
        <div class="bg-white shadow-md p-4 rounded-md cursor-pointer" onclick="toggleDetails('details1')">
            <p><strong>$matiere | $classe | $salle</strong></p>
            <p class="flex justify-end"><strong>$heureString - $heurefinString</strong></p>
            <p class="flex justify-end"><strong>$date</strong></p>
            <p class="text-sm text-gray-900"><strong>$prenom - $nom</strong></p>
        </div>
        <div id="details1" class="bg-gray-200 p-4 rounded-md overflow-hidden transition-all duration-300 ease-in-out opacity-0" style="height: 0px;">
            <p>$info</p>
        </div>
    </div>
    HTML;
        
}
?>      
        <!-- Tableau 
        <div class="space-y-4">
            <div class="bg-white shadow-md p-4 rounded-md cursor-pointer" onclick="toggleDetails('details1')">
                <p><strong>Matière | Classe concernée | Salle</strong></p>
                <p class="flex justify-end"><strong>HeureDEBUT-HeureFIN</strong></p>
                <p class="flex justify-end"><strong>Date(j-m)</strong></p>
                <p class="text-sm text-gray-900"><strong>Prof</strong></p>
            </div>
            <div id="details1" class="bg-gray-200 p-4 rounded-md overflow-hidden transition-all duration-300 ease-in-out opacity-0" style="height: 0px;">
                <p>Informations générales sur la séance...</p>
            </div>
            
            <div class="bg-blue-400 text-white p-4 rounded-md cursor-pointer" onclick="toggleDetails('details2')">
                <p><strong>Matière | Classe concernée | Salle</strong></p>
                <p class="flex justify-end"><strong>HeureDEBUT-HeureFIN</strong></p>
                <p class="flex justify-end"><strong>Date(j-m)</strong></p>
                <p class="text-sm"><strong>Prof</strong></p>
            </div>
            <div id="details2" class="bg-gray-200 p-4 rounded-md overflow-hidden transition-all duration-300 ease-in-out opacity-0" style="height: 0px;">
                <p>Informations générales sur la séance...</p>
            </div>
            
            <div class="bg-white shadow-md p-4 rounded-md cursor-pointer" onclick="toggleDetails('details3')">
                <p><strong>Matière | Classe concernée | Salle</strong></p>
                <p class="flex justify-end"><strong>HeureDEBUT-HeureFIN</strong></p>
                <p class="flex justify-end"><strong>Date(j-m)</strong></p>
                <p class="text-sm text-gray-900"><strong>Prof</strong></p>
            </div>
            <div id="details3" class="bg-gray-200 p-4 rounded-md overflow-hidden transition-all duration-300 ease-in-out opacity-0" style="height: 0px;">
                <p>Informations générales sur la séance...</p>
            </div>
            
            <div class="bg-blue-400 p-4 text-white rounded-md cursor-pointer" onclick="toggleDetails('details4')">
                <p><strong>Matière | Classe concernée | Salle</strong></p>
                <p class="flex justify-end"><strong>HeureDEBUT-HeureFIN</strong></p>
                <p class="flex justify-end"><strong>Date(j-m)</strong></p>
                <p class="text-sm"><strong>Prof</strong></p>
            </div>
            <div id="details4" class="bg-gray-200 p-4 rounded-md overflow-hidden transition-all duration-300 ease-in-out opacity-0" style="height: 0px;">
                <p>Informations générales sur la séance...</p>
            </div>
        </div>
        -->
    </div>
</body>
</html>
