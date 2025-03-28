<?php
$request_reservation = "http://192.168.8.152:8000/reservations/?croissant=true";
$response_reservation = file_get_contents($request_reservation);
$data = json_decode($response_reservation, true);

// Fonction pour formater l'heure à partir d'un intervalle
function formaterHeure($interval, $duree = 0) {
    $intervalObj = new DateInterval($interval);
    $heures = $intervalObj->h;
    $minutes = $intervalObj->i;
    
    $heureFloat = $heures + ($minutes / 60);
    
    if ($duree > 0) {
        $heuresFin = floor($heureFloat + $duree);
        $minutesFin = round(($heureFloat + $duree - $heuresFin) * 60);
        return sprintf("%02d:%02d", $heuresFin, $minutesFin);
    }
    
    return sprintf("%02d:%02d", $heures, $minutes);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservations - Système d'information BTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.25);
        }
        .card-details {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease-in-out, opacity 0.3s ease;
        }
        .animate-pulse-light {
            animation: pulse-light 2s infinite;
        }
        @keyframes pulse-light {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
    </style>
    <script>
        function toggleDetails(id) {
            const details = document.getElementById(id);
            const icon = document.getElementById(`icon-${id}`);
            
            if (details.style.maxHeight === "" || details.style.maxHeight === "0px") {
                details.style.maxHeight = "200px"; // Hauteur maximale pour l'animation
                details.classList.remove("opacity-0");
                details.classList.add("opacity-100");
                icon.classList.replace("fa-chevron-down", "fa-chevron-up");
            } else {
                details.style.maxHeight = "0px";
                details.classList.remove("opacity-100");
                details.classList.add("opacity-0");
                icon.classList.replace("fa-chevron-up", "fa-chevron-down");
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen">
    
    <!-- Barre de navigation -->
    <header class="bg-gradient-to-r from-indigo-700 to-indigo-500 text-white p-4 flex justify-between items-center w-full fixed top-0 left-0 right-0 shadow-lg z-10">
        <div class="flex items-center space-x-2">
            <i class="fas fa-calendar-alt text-xl"></i>
            <h1 class="text-xl font-bold">Système d'information BTS</h1>
        </div>
        <a href="interface_login.php">
            <button class="bg-white px-4 py-2 rounded-md text-indigo-600 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-400 transition-all shadow-md font-semibold flex items-center space-x-1">
                <i class="fas fa-sign-in-alt"></i>
                <span>Se connecter</span>
            </button>
        </a>
    </header>
    
    <div class="w-full max-w-6xl mx-auto px-4 pt-24 pb-10">
        <!-- Titre principal -->
        <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold text-indigo-800 mb-2">Tableau Prévisionnel des Séances</h2>
            <div class="h-1 w-20 bg-indigo-500 mx-auto rounded-full"></div>
        </div>
        
        <!-- Carte d'information -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg shadow-md mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-500"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        Cliquez sur une réservation pour afficher plus de détails. Mises à jour en temps réel.
                    </p>
                </div>
            </div>
        </div>

        <!-- Container de réservations -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <?php foreach($data as $index => $reservation): 
                // Formatage des données
                $matiere = $reservation['nom_matiere'];
                $salle = $reservation['numero_salle'];
                $classe = $reservation['noms_classes'];
                $prenom = $reservation['prenom'];
                $nom = $reservation['nom_user'];
                $info = $reservation['info'] ?: "Aucune information complémentaire.";
                
                // Formatage de la date
                $dt = new DateTime($reservation['date']);
                $jour = $dt->format("d");
                $mois = $dt->format("m");
                $nomJour = $dt->format("D");
                
                // Traduction du jour en français
                $joursTraduction = [
                    'Mon' => 'Lun',
                    'Tue' => 'Mar',
                    'Wed' => 'Mer',
                    'Thu' => 'Jeu',
                    'Fri' => 'Ven',
                    'Sat' => 'Sam',
                    'Sun' => 'Dim'
                ];
                $jourFr = $joursTraduction[$nomJour] ?? $nomJour;
                
                // Formatage des heures
                $heureDebut = formaterHeure($reservation['heure_debut']);
                $heureFin = formaterHeure($reservation['heure_debut'], $reservation['duree']);
                
                // ID unique pour les détails
                $detailsID = "details_" . ($index + 1);
                
                // Choix des couleurs (alternance)
                $bgColor = $index % 2 == 0 
                    ? "bg-white hover:bg-indigo-50" 
                    : "bg-gradient-to-r from-indigo-600 to-indigo-500 text-white hover:from-indigo-500 hover:to-indigo-400";
                
                $textColor = $index % 2 == 0 ? "text-indigo-900" : "text-white";
                $timeColor = $index % 2 == 0 ? "text-indigo-600" : "text-indigo-100";
                $borderColor = $index % 2 == 0 ? "border-indigo-200" : "border-indigo-400";
            ?>
            
            <div class="space-y-1">
                <!-- Carte de réservation -->
                <div class="card-hover <?= $bgColor ?> shadow-md rounded-lg overflow-hidden border <?= $borderColor ?>">
                    <div class="p-4 cursor-pointer" onclick="toggleDetails('<?= $detailsID ?>')">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex items-center space-x-2">
                                <span class="<?= $textColor ?> font-bold"><?= htmlspecialchars($matiere) ?></span>
                                <span class="px-2 py-0.5 rounded text-xs bg-opacity-20 <?= $index % 2 == 0 ? 'bg-indigo-500 text-indigo-800' : 'bg-white text-indigo-800' ?>"><?= htmlspecialchars($classe) ?></span>
                            </div>
                            <div class="<?= $timeColor ?> font-semibold text-right">
                                <?= $heureDebut ?> - <?= $heureFin ?>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-map-marker-alt <?= $index % 2 == 0 ? 'text-indigo-400' : 'text-indigo-200' ?>"></i>
                                <span class="<?= $index % 2 == 0 ? 'text-indigo-600' : 'text-indigo-100' ?>"><?= htmlspecialchars($salle) ?></span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="flex flex-col items-center justify-center <?= $index % 2 == 0 ? 'bg-indigo-100' : 'bg-indigo-400' ?> rounded-md w-10 h-10">
                                    <span class="text-xs <?= $index % 2 == 0 ? 'text-indigo-700' : 'text-white' ?>"><?= $jourFr ?></span>
                                    <span class="text-sm font-bold <?= $index % 2 == 0 ? 'text-indigo-700' : 'text-white' ?>"><?= $jour ?>/<?= $mois ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center mt-2">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-user <?= $index % 2 == 0 ? 'text-indigo-400' : 'text-indigo-200' ?>"></i>
                                <span class="<?= $index % 2 == 0 ? 'text-gray-600' : 'text-indigo-100' ?> text-sm"><?= htmlspecialchars("$prenom $nom") ?></span>
                            </div>
                            <i id="icon-<?= $detailsID ?>" class="fas fa-chevron-down <?= $index % 2 == 0 ? 'text-indigo-400' : 'text-indigo-200' ?>"></i>
                        </div>
                    </div>
                    
                    <!-- Section de détails cachée -->
                    <div id="<?= $detailsID ?>" class="card-details opacity-0 bg-indigo-50 p-4 border-t border-indigo-200">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-indigo-500 mt-1 mr-2"></i>
                            <div class="text-gray-700 text-sm"><?= nl2br(htmlspecialchars($info)) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php endforeach; ?>
            
            <?php if (empty($data)): ?>
            <div class="col-span-2 text-center p-10">
                <div class="text-gray-500">
                    <i class="fas fa-calendar-times text-4xl mb-3"></i>
                    <p>Aucune réservation à afficher pour le moment.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Indicateur de mise à jour -->
        <div class="text-center mt-6">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-indigo-100 text-indigo-800">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse-light mr-2"></span>
                Dernière mise à jour: <?= date('d/m/Y à H:i') ?>
            </span>
        </div>
    </div>
    
    <footer class="bg-gradient-to-r from-indigo-800 to-indigo-700 text-white py-6 shadow-inner">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <p class="font-semibold">© 2025 Système d'information BTS</p>
                    <p class="text-xs text-indigo-200">Tous droits réservés</p>
                </div>
                <div class="flex space-x-4">
                    <a href="#" class="text-indigo-200 hover:text-white transition-colors">Mentions légales</a>
                    <a href="#" class="text-indigo-200 hover:text-white transition-colors">Aide</a>
                    <a href="#" class="text-indigo-200 hover:text-white transition-colors">Contact</a>
                </div>
            </div>
        </div>
    </footer>
    
</body>
</html>
