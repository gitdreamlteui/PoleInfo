<?php
$request_reservation = "http://192.168.8.152:8000/reservations"; // Remplace par l'URL de l'API
$request_user = "http://192.168.8.152:8000/users";
$request_salle = "http://192.168.8.152:8000/salles";
$request_creneau = "http://192.168.8.152:8000/creneaux";
$request_matiere = "http://192.168.8.152:8000/matieres";
$request_classe = "http://192.168.8.152:8000/classes";
$request_classe_reservation = "http://192.168.8.152:8000/classes_reservation";

$response_reservation = file_get_contents($request_reservation);
$response_user = file_get_contents($request_user);
$response_salle = file_get_contents($request_salle);
$response_creneau = file_get_contents($request_creneau);
$response_matiere = file_get_contents($request_matiere);
$response_classe = file_get_contents($request_classe);
$response_classe_reservation = file_get_contents($request_classe_reservation);

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
    <header class="bg-gray-800 text-white p-4 flex justify-between items-center w-full fixed top-0 left-0 right-0">
        <h1 class="text-xl font-bold">Réservation</h1>
        <a href="interface_login.php">
            <button class="bg-blue-500 px-4 py-2 rounded-md">Login</button>
        </a>
    </header>
    
    <div class="max-w-3xl mx-auto mt-16">
        <!-- Titre -->
        <div class="bg-gray-800 text-white text-xl font-bold p-4 rounded-md mb-4">
            Tableau Prévisionnel des séances à venir
        </div> 
<?
?>       
        <!-- Tableau -->
        <div class="space-y-4">
            <div class="bg-blue-400 text-white p-4 rounded-md cursor-pointer" onclick="toggleDetails('details1')">
                <p><strong>Matière | Classe concernée | Salle</strong></p>
                <p class="flex justify-end"><strong>Heure</strong></p>
                <p class="flex justify-end"><strong>Date(j-m)</strong></p>
                <p class="text-sm text-blue-900">Prof</p>
                </div>
            <div id="details1" class="bg-white-200 p-4 rounded-md overflow-hidden transition-all duration-300 ease-in-out opacity-0" style="height: 0px;">
                <p>Informations générales sur la séance...</p>
            </div>
            
            <div class="bg-white shadow-md p-4 rounded-md cursor-pointer" onclick="toggleDetails('details2')">
                <p><strong>Matière | Classe concernée | Salle</strong></p>
                <p class="flex justify-end"><strong>Heure</strong></p>
                <p class="flex justify-end"><strong>Date(j-m)</strong></p>
                <p class="text-sm text-gray-500">Prof</p>
            </div>
            <div id="details2" class="bg-blue-200 p-4 rounded-md overflow-hidden transition-all duration-300 ease-in-out opacity-0" style="height: 0px;">
                <p>Informations générales sur la séance...</p>
            </div>
            
            <div class="bg-blue-400 text-white p-4 rounded-md cursor-pointer" onclick="toggleDetails('details3')">
                <p><strong>Matière | Classe concernée | Salle</strong></p>
                <p class="flex justify-end"><strong>Heure</strong></p>
                <p class="flex justify-end"><strong>Date(j-m)</strong></p>
                <p class="text-sm text-blue-900">Prof</p>
            </div>
            <div id="details3" class="bg-white-200 p-4 rounded-md overflow-hidden transition-all duration-300 ease-in-out opacity-0" style="height: 0px;">
                <p>Informations générales sur la séance...</p>
            </div>
            
            <div class="bg-white shadow-md p-4 rounded-md cursor-pointer" onclick="toggleDetails('details4')">
                <p><strong>Matière | Classe concernée | Salle</strong></p>
                <p class="flex justify-end"><strong>Heure</strong></p>
                <p class="flex justify-end"><strong>Date(j-m)</strong></p>
                <p class="text-sm text-gray-500">Prof</p>
            </div>
            <div id="details4" class="bg-blue-200 p-4 rounded-md overflow-hidden transition-all duration-300 ease-in-out opacity-0" style="height: 0px;">
                <p>Informations générales sur la séance...</p>
            </div>
        </div>
        
    </div>
</body>
</html>
