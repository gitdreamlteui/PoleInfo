<?php
// INDEX.PHP
require_once __DIR__ . '/config.php';
$connected = FALSE;

$request_reservation = getApiUrl("/reservations/?croissant=true");
$response_reservation = file_get_contents($request_reservation);
$data = json_decode($response_reservation, true);

$date_actuelle = new DateTime();
$heure_actuelle = $date_actuelle->format('H:i');
$date_jour = $date_actuelle->format('d/m/Y');


session_start();
// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['token'])) {
    $token = $_SESSION['token'];
    $username = $_SESSION['username'];
    $login = $_SESSION['login'];
    $type = $_SESSION['type_compte'];

    $api_url_verify = getApiUrl('/verify-token/');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_URL, $api_url_verify);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        $connected = TRUE;
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - Système d'information BTS</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { primary: { DEFAULT: '#1a4d85', light: '#e6f0ff' } },
                    fontFamily: { inter: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        .clock-display {
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.5px;
        }
    </style>
    <script>
        function toggleDetails(id) {
            document.getElementById(id).classList.toggle('hidden');
        }

        function updateClock() {
            const now = new Date();
            const time = [now.getHours(), now.getMinutes(), now.getSeconds()]
                .map(n => n.toString().padStart(2, '0'))
                .join(':');
            document.getElementById('clock').textContent = time;
            setTimeout(updateClock, 1000);
        }

        window.onload = updateClock;
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function actualiserReservations() {
            $.ajax({
                url: window.location.href,
                success: function(data) {
                    var nouvellesDonnees = $(data).find('#reservations-container').html();
                    $('#reservations-container').html(nouvellesDonnees);
                },
                complete: function() {
                    setTimeout(actualiserReservations, 10000);
                }
            });
        }

        $(document).ready(function() {
            setTimeout(actualiserReservations, 10000);
        });
    </script>
</head>
<body class="bg-gray-50 font-inter text-gray-800 m-0 p-0">
    <header class="bg-primary fixed top-0 w-full py-3 px-4 shadow-md z-10">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center">
                <div class="font-semibold text-white text-2xl">Système d'information BTS - Pôle Info</div>
            </div>
            <?php if (isset($connected) && $connected === TRUE): ?>
                <div class="flex items-center">
                    <div class="text-white mr-4">Bonjour, <?php echo $login; ?></div>
                    <?php if (isset($type) && $type == 1): ?>
                        <a href="admin/interface_admin.php" class="no-underline">
                            <button class="bg-white text-primary font-semibold py-2 px-4 rounded hover:bg-blue-50 transition-colors">
                                Accéder à mon espace
                            </button>
                        </a>
                    <?php else: ?>
                        <a href="user/dashboard.php" class="no-underline">
                            <button class="bg-white text-primary font-semibold py-2 px-4 rounded hover:bg-blue-50 transition-colors">
                                Accéder à mon espace
                            </button>
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <a href="interface_login.php" class="no-underline">
                    <button class="bg-white text-primary font-semibold py-2 px-4 rounded hover:bg-blue-50 transition-colors">
                        Se connecter
                    </button>
                </a>
            <?php endif; ?>
        </div>
    </header>


    
    <main class="container mx-auto px-4 py-6 mt-16">
        <div class="bg-white p-3 mb-6 rounded-lg shadow-sm border border-gray-200 flex justify-between items-center">
            <div class="text-gray-600">
                <span class="font-medium">Aujourd'hui : </span>
                <span><?php echo $date_jour; ?></span>
            </div>
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium text-primary mr-2">Heure actuelle :</span>
                <span id="clock" class="clock-display font-medium bg-primary text-white px-3 py-1 rounded-md">
                    <?php echo $heure_actuelle; ?>
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="bg-primary text-white p-3 mb-4 font-semibold text-lg rounded-lg shadow">
                    Tableau Prévisionnel des séances à venir
                </div>
                
                <div class="space-y-3" id="reservations-container">
                    <?php
                    $compteur = 0;
                    foreach($data as $item){
                        $compteur++;
                        
                        // Traitement de l'heure début
                        $interval = new DateInterval($item['heure_debut']);
                        $heures = $interval->h;
                        $minutes = $interval->i;
                        $heureString = sprintf("%02d:%02d", $heures, $minutes);
                        $heureFloat = $heures + ($minutes / 60);
                        
                        // Traitement heure fin
                        $heuresfin = floor($heureFloat + $item['duree']);
                        $rawminutes = ($heureFloat + $item['duree'] - $heuresfin) * 60;
                        $minutesfin = (int) round($rawminutes);
                        if(($heures<10&&$heuresfin>=10)||($heures<15&&$heuresfin>=15))
                        {
                            $minutesfin+=15;
                        }
                        $heurefinString = sprintf("%02d:%02d", $heuresfin, $minutesfin);
                        
                        // Traitement de la date
                        $dt = new DateTime($item['date']);
                        $date = $dt->format("j/m");
                        
                        $detailsID = "details_$compteur";
                        $bgColor = ($compteur % 2 == 1) ? "bg-primary-light" : "bg-primary text-white";
                        $badgeBg = ($compteur % 2 == 1) ? "bg-primary bg-opacity-10" : "bg-white bg-opacity-20";
                        
                        echo <<<HTML
                        <div class="rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-transform hover:-translate-y-0.5">
                            <div class="$bgColor p-3 cursor-pointer" onclick="toggleDetails('$detailsID')">
                                <div class="flex justify-between items-center">
                                    <div class="font-semibold">
                                        <span>{$item['nom_matiere']}</span>
                                        <span class="opacity-70 mx-1.5">•</span>
                                        <span>{$item['noms_classes']}</span>
                                    </div>
                                    <div class="font-semibold flex items-center">
                                        $heureString - $heurefinString
                                        <span class="$badgeBg ml-2 px-2 py-0.5 text-sm rounded">$date</span>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center mt-2">
                                    <div class="text-sm font-medium opacity-90">{$item['prenom']} {$item['nom_user']}</div>
                                    <div class="$badgeBg px-2 py-0.5 text-sm rounded font-medium">Salle {$item['numero_salle']}</div>
                                </div>
                            </div>
                            <div id="$detailsID" class="bg-white p-4 border-t border-gray-200 hidden">
                                <p class="leading-relaxed">{$item['info']}</p>
                            </div>
                        </div>
                        HTML;
                    }
                    ?>
                </div>

            </div>
            
            <div class="lg:col-span-1">
                <div class="bg-primary text-white p-3 mb-4 font-semibold text-lg rounded-lg shadow">
                    Actualités & Messages
                </div>
                
                <div class="space-y-4">               
                    <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-green-500">
                        <h3 class="font-semibold text-lg text-primary mb-2">Bienvenue !</h3>
                        <p class="text-gray-600 mb-2">L'équipe de développement du système d'information Pôle Info vous souhaite la bienvenue.</p>
                        <div class="flex justify-between items-center text-sm text-gray-500">
                            <span>Service développement</span>
                            <span>28/03/2025</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="text-center text-sm text-gray-500 mt-8 border-t border-gray-200 pt-4">
            © 2025 Système d'information BTS - Tous droits réservés
        </footer>
    </main>
</body>
</html>
