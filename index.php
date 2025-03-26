<?php
$api = "http://192.168.8.152:8000/reservations"; // Remplace par l'URL de l'API
$response = file_get_contents($api);
$data = json_decode($response, true);
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
            details.classList.toggle("hidden");
        }
    </script>
</head>
<body class="p-10 bg-gray-100">
    
    <!-- Barre de navigation -->
    <header class="bg-gray-800 text-white p-4 flex justify-between items-center w-full fixed top-0 left-0 right-0">
        <h1 class="text-xl font-bold">Réservation</h1>
        <button class="bg-blue-500 px-4 py-2 rounded-md" src="interface_login.php">Login</button>
    </header>
    
    <div class="max-w-3xl mx-auto mt-16">
        <!-- Titre -->
        <div class="bg-gray-800 text-white text-xl font-bold p-4 rounded-md mb-4">
            Tableau Prévisionnel des séances à venir
        </div> 
<?
echo $response;
echo $data;
?>       
        <!-- Tableau -->
        <div class="space-y-4">
            <div class="bg-blue-400 text-white p-4 rounded-md cursor-pointer" onclick="toggleDetails('details1')">
                <p><strong>Matière | Classe concernée | Salle</strong></p>
                <p class="text-sm text-blue-900">Prof</p>
            </div>
            <div id="details1" class="hidden bg-blue-200 p-4 rounded-md">
                <p>Informations générales sur la séance...</p>
            </div>
            
            <div class="bg-white shadow-md p-4 rounded-md cursor-pointer" onclick="toggleDetails('details2')">
                <p><strong>Matière | Classe concernée | Salle</strong></p>
                <p class="text-sm text-gray-500">Prof</p>
            </div>
            <div id="details2" class="hidden bg-gray-200 p-4 rounded-md">
                <p>Informations générales sur la séance...</p>
            </div>
            
            <div class="bg-blue-400 text-white p-4 rounded-md cursor-pointer" onclick="toggleDetails('details3')">
                <p><strong>Matière | Classe concernée | Salle</strong></p>
                <p class="text-sm text-blue-900">Prof</p>
            </div>
            <div id="details3" class="hidden bg-blue-200 p-4 rounded-md">
                <p>Informations générales sur la séance...</p>
            </div>
            
            <div class="bg-white shadow-md p-4 rounded-md cursor-pointer" onclick="toggleDetails('details4')">
                <p><strong>Matière | Classe concernée | Salle</strong></p>
                <p class="text-sm text-gray-500">Prof</p>
            </div>
            <div id="details4" class="hidden bg-gray-200 p-4 rounded-md">
                <p>Informations générales sur la séance...</p>
            </div>
        </div>
        
    </div>
</body>
</html>
