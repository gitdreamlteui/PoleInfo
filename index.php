<?php
$request_reservation = "http://192.168.8.152:8000/reservations/?croissant=true"; // Remplace par l'URL de l'API
$response_reservation = file_get_contents($request_reservation);
$data = json_decode($response_reservation, true);
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
            const currentHeight = details.style.height;
            
            if (currentHeight === "" || currentHeight === "0px") {
                details.style.height = details.scrollHeight + "px";
                details.classList.remove("opacity-0", "max-h-0");
                details.classList.add("opacity-100", "max-h-96");
            } else {
                details.style.height = "0px";
                details.classList.remove("opacity-100", "max-h-96");
                details.classList.add("opacity-0", "max-h-0");
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Barre de navigation -->
    <header class="bg-[#4B0082] text-white p-4 flex justify-between items-center w-full fixed top-0 left-0 right-0 z-50 shadow-lg">
        <h1 class="text-xl font-bold">Système d'information BTS - Réservation</h1>
        <a href="interface_login.php">
            <button class="bg-white px-4 py-2 rounded-md text-[#4B0082] hover:bg-[#A7C7E7] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#6A5ACD] transition-colors shadow-md font-semibold">
                Se connecter
            </button>
        </a>
    </header>
    
    <main class="w-full px-4 mt-24 max-w-4xl mx-auto">
        <!-- Titre -->
        <div class="bg-[#191970] text-white text-xl font-bold p-4 rounded-lg mb-6 shadow-lg">
            Tableau Prévisionnel des séances à venir
        </div> 

        <div class="space-y-4">
        <?php
        $compteur = 0;
        foreach($data as $reservation) {
            $compteur++;
            
            // Extraction des données
            $matiere = htmlspecialchars($reservation['nom_matiere']);
            $salle = htmlspecialchars($reservation['numero_salle']);
            $date = htmlspecialchars($reservation['date']);
            $info = htmlspecialchars($reservation['info']);
            $classe = htmlspecialchars($reservation['noms_classes']);
            $prenom = htmlspecialchars($reservation['prenom']);
            $nom = htmlspecialchars($reservation['nom_user']);
            $debut = $reservation['heure_debut'];
            $duree = $reservation['duree'];

            // Traitement des heures
            $interval = new DateInterval($debut);
            $heures = $interval->h;
            $minutes = $interval->i;
            $heureString = sprintf("%02d:%02d", $heures, $minutes);
            $heureFloat = $heures + ($minutes / 60);
            
            $heuresfin = floor($heureFloat + $duree);
            $minutesfin = round(($heureFloat + $duree - $heuresfin) * 60);
            $heurefinString = sprintf("%02d:%02d", $heuresfin, $minutesfin);
            
            // Traitement de la date
            $dt = new DateTime($date);
            $dateFormatted = $dt->format("j/m");
            
            $detailsID = "details_$compteur";
            
            // Alternance des styles
            $bgColor = ($compteur % 2 == 1) 
                ? "bg-white hover:bg-[#E6E6FA]" 
                : "bg-[#6A5ACD] text-white hover:bg-[#4B0082]";
            
            $textColor = ($compteur % 2 == 1) 
                ? "text-gray-800" 
                : "text-white";
            
            echo <<<HTML
            <div class="w-full space-y-4 mb-4">
                <div class="$bgColor shadow-lg p-4 rounded-lg cursor-pointer transition-colors" onclick="toggleDetails('$detailsID')">
                    <p class="$textColor"><strong>$matiere | $classe | $salle</strong></p>
                    <div class="flex justify-between items-center">
                        <p class="text-sm $textColor"><strong>$prenom $nom</strong></p>
                        <div class="$textColor">
                            <p><strong>$heureString - $heurefinString</strong></p>
                            <p><strong>$dateFormatted</strong></p>
                        </div>
                    </div>
                </div>
                <div id="$detailsID" class="bg-gray-100 p-4 rounded-lg overflow-hidden transition-all duration-300 ease-in-out opacity-0 max-h-0" style="height: 0px;">
                    <p class="text-gray-700">$info</p>
                </div>
            </div>
            HTML;
        }
        ?>
        </div>
    </main>
    
    <footer class="mt-6 text-center text-sm text-gray-500 py-4">
        <p>© 2025 Système d'information BTS - Tous droits réservés</p>
    </footer>
</body>
</html>