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
    <title>Tableau des Réservations - Système d'information BTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Polices plus lisibles pour l'affichage */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            --primary: #4f46e5;
            --primary-light: #818cf8;
            --primary-lighter: #e0e7ff;
            --secondary: #1e1b4b;
        }
        
        /* Animation de mise à jour des informations */
        @keyframes highlight {
            0% { background-color: rgba(79, 70, 229, 0.2); }
            100% { background-color: transparent; }
        }
        
        .info-update {
            animation: highlight 2s ease-out;
        }
        
        /* Style pour l'horloge */
        .time-display {
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.05em;
        }
        
        /* Entêtes de section avec dégradé */
        .section-header {
            background: linear-gradient(to right, var(--secondary), var(--primary));
        }

        /* Pour améliorer la lisibilité sur les grands écrans d'affichage */
        .reservation-card {
            transition: all 0.3s ease;
        }
        
        .reservation-card:hover {
            transform: scale(1.01);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        /* Animation pour le chargement des réservations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }

        /* Animation pour les détails */
        .details-container {
            overflow: hidden;
            transition: height 0.3s ease, opacity 0.3s ease, padding 0.3s ease;
        }
    </style>
</head>
<body class="bg-indigo-50 min-h-screen flex flex-col">
    <!-- Entête fixe -->
    <header class="bg-indigo-900 text-white shadow-lg sticky top-0 z-10">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <i class="fas fa-calendar-alt text-2xl text-indigo-300"></i>
                <h1 class="text-xl lg:text-2xl font-bold tracking-wide">Système d'Information BTS</h1>
            </div>
            <div class="flex items-center gap-4">
                <div class="time-display text-xl font-semibold hidden md:block" id="clock">00:00:00</div>
                <a href="interface_login.php">
                    <button class="bg-white px-4 py-2 rounded-md text-indigo-600 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors shadow-md font-semibold flex items-center">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Se connecter
                    </button>
                </a>
            </div>
        </div>
    </header>

    <!-- Bannière d'information -->
    <div class="bg-indigo-600 text-white px-4 py-3 text-center text-lg font-medium shadow">
        <div class="container mx-auto">
            <i class="fas fa-bullhorn mr-2"></i> 
            <span id="announcement">Planning des réservations de salles</span>
        </div>
    </div>

    <!-- Filtre rapide (jour actuel, semaine) -->
    <div class="container mx-auto px-4 py-4 flex flex-wrap gap-2 items-center">
        <div class="text-lg font-semibold text-indigo-800 mr-2">Filtre rapide :</div>
        <button id="btn-today" class="bg-indigo-100 hover:bg-indigo-200 text-indigo-800 px-4 py-2 rounded-lg font-medium transition-colors">
            <i class="fas fa-calendar-day mr-1"></i> Aujourd'hui
        </button>
        <button id="btn-tomorrow" class="bg-indigo-100 hover:bg-indigo-200 text-indigo-800 px-4 py-2 rounded-lg font-medium transition-colors">
            <i class="fas fa-arrow-right mr-1"></i> Demain
        </button>
        <button id="btn-week" class="bg-indigo-100 hover:bg-indigo-200 text-indigo-800 px-4 py-2 rounded-lg font-medium transition-colors">
            <i class="fas fa-calendar-week mr-1"></i> Cette semaine
        </button>
        <button id="btn-all" class="bg-indigo-500 text-white px-4 py-2 rounded-lg font-medium transition-colors hover:bg-indigo-600">
            <i class="fas fa-list mr-1"></i> Toutes
        </button>
        
        <div class="ml-auto mt-2 md:mt-0">
            <span class="text-indigo-700 font-medium">Dernière mise à jour :</span> 
            <span id="last-update" class="text-indigo-900 font-semibold"></span>
        </div>
    </div>

    <!-- Titre et statistiques -->
    <div class="container mx-auto px-4 py-4">
        <div class="bg-gradient-to-r from-indigo-800 to-indigo-600 rounded-xl shadow-lg p-6 text-white mb-6">
            <h2 class="text-2xl lg:text-3xl font-bold mb-2">Tableau des Réservations</h2>
            <p class="opacity-90 mb-4">Consultez les réservations de salles à venir</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                <div class="bg-white/20 rounded-lg p-3 backdrop-blur-sm">
                    <div class="text-lg font-semibold"><i class="fas fa-calendar-check mr-2"></i> <span id="total-count"><?php echo count($data); ?></span></div>
                    <div class="text-sm opacity-75">Réservations au total</div>
                </div>
                <div class="bg-white/20 rounded-lg p-3 backdrop-blur-sm">
                    <div class="text-lg font-semibold"><i class="fas fa-calendar-day mr-2"></i> <span id="today-count">0</span></div>
                    <div class="text-sm opacity-75">Réservations aujourd'hui</div>
                </div>
                <div class="bg-white/20 rounded-lg p-3 backdrop-blur-sm">
                    <div class="text-lg font-semibold"><i class="fas fa-door-open mr-2"></i> <span id="rooms-count">0</span></div>
                    <div class="text-sm opacity-75">Salles différentes</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grille des réservations -->
    <div class="container mx-auto px-4 py-2 flex-1">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" id="reservation-grid">
            <?php
            $compteur = 0;
            $unique_rooms = [];
            $today_count = 0;
            $current_date = date("Y-m-d"); // Format de date pour comparaison
            
            foreach($data as $index => $reservation) {
                $compteur++;
                $matiere = $reservation['nom_matiere'];
                $salle = $reservation['numero_salle'];
                $date = $reservation['date'];
                $info = $reservation['info'] ?? 'Aucune information complémentaire';
                $classe = $reservation['noms_classes'];
                $prenom = $reservation['prenom'];
                $nom = $reservation['nom_user'];
                $debut = $reservation['heure_debut'];
                $duree = $reservation['duree'];
                
                // Ajouter à la liste des salles uniques
                if (!in_array($salle, $unique_rooms)) {
                    $unique_rooms[] = $salle;
                }
                
                // Traitement de l'heure debut
                $interval = new DateInterval($debut);
                $heures = $interval->h;
                $minutes = $interval->i;
                $heureString = sprintf("%02d:%02d", $heures, $minutes);
                $heureFloat = $heures + ($minutes / 60);
                
                // Traitement heure fin
                $heuresfin = floor($heureFloat + $duree);
                $minutesfin = ($heureFloat + $duree - $heuresfin) * 60;  // Partie décimale convertie en minutes
                $heurefinString = sprintf("%02d:%02d", $heuresfin, $minutesfin);
                
                // Traitement de la date pour l'affichage
                $dt = new DateTime($date);
                $date_display = $dt->format("j/m");
                $date_full = $dt->format("j F Y");
                $jour_semaine = $dt->format("l");
                
                // Traduction du jour de la semaine
                $jours_fr = [
                    'Monday' => 'Lundi',
                    'Tuesday' => 'Mardi',
                    'Wednesday' => 'Mercredi',
                    'Thursday' => 'Jeudi',
                    'Friday' => 'Vendredi',
                    'Saturday' => 'Samedi',
                    'Sunday' => 'Dimanche'
                ];
                $jour_fr = $jours_fr[$jour_semaine];
                
                // Format pour data-date pour le filtrage JavaScript
                $date_filter = $dt->format("Y-m-d");
                
                // Vérifier si la réservation est pour aujourd'hui
                if ($date_filter === $current_date) {
                    $today_count++;
                }
                
                // ID pour les détails
                $detailsID = "details_$compteur";
                
                // Déterminer le style en fonction du compteur
                $card_style = $compteur % 2 == 0 
                    ? "bg-indigo-600 text-white hover:bg-indigo-700" 
                    : "bg-white hover:bg-indigo-50 border border-indigo-100";
                
                $text_style = $compteur % 2 == 0 
                    ? "text-white" 
                    : "text-indigo-700";
                
                $delay = ($index % 10) * 0.05; // Animation staggerée pour les cartes
                
                echo <<<HTML
                <div class="reservation-card fade-in $card_style rounded-xl shadow-lg overflow-hidden" 
                     style="animation-delay: {$delay}s;" data-date="$date_filter">
                    <div class="p-4 cursor-pointer" onclick="toggleDetails('$detailsID')">
                        <div class="flex justify-between items-center mb-3">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-indigo-800 text-white">$jour_fr $date_display</span>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-indigo-800 text-white">$heureString - $heurefinString</span>
                        </div>
                        
                        <h3 class="text-xl font-bold mb-1 flex items-center">
                            <i class="fas fa-book-open mr-2"></i>$matiere
                        </h3>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 my-3">
                            <div class="flex items-center $text_style">
                                <i class="fas fa-users mr-2"></i>
                                <span>$classe</span>
                            </div>
                            <div class="flex items-center $text_style">
                                <i class="fas fa-door-open mr-2"></i>
                                <span>Salle $salle</span>
                            </div>
                            <div class="flex items-center $text_style">
                                <i class="fas fa-chalkboard-teacher mr-2"></i>
                                <span>$prenom $nom</span>
                            </div>
                        </div>
                        
                        <div class="text-right">
                            <button class="text-xs font-medium underline focus:outline-none">
                                Détails <i class="fas fa-chevron-down ml-1"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div id="$detailsID" class="details-container bg-indigo-50 border-t border-indigo-200 p-0 opacity-0" style="height: 0px;">
                        <div class="p-4">
                            <h4 class="font-semibold text-indigo-800 mb-2">Informations complémentaires</h4>
                            <p class="text-gray-700">$info</p>
                        </div>
                    </div>
                </div>
                HTML;
            }
            ?>
        </div>
        
        <!-- Message si aucune réservation -->
        <div id="no-results" class="hidden bg-white rounded-lg shadow-lg p-8 text-center my-8">
            <i class="fas fa-calendar-times text-indigo-300 text-5xl mb-4"></i>
            <h3 class="text-xl font-semibold text-indigo-800 mb-2">Aucune réservation trouvée</h3>
            <p class="text-gray-600">Il n'y a pas de réservation correspondant à vos critères de filtrage.</p>
            <button id="reset-filter" class="mt-4 bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                Afficher toutes les réservations
            </button>
        </div>
    </div>

    <!-- Pied de page -->
    <footer class="bg-indigo-900 text-white py-4 mt-auto">
        <div class="container mx-auto px-4 text-center">
            <p>© 2025 Système d'information BTS - Tous droits réservés</p>
            <p class="mt-1 text-indigo-300 text-sm">Pour signaler un problème avec le système de réservation, contactez l'administrateur</p>
        </div>
    </footer>

    <script>
        // Initialisation des compteurs
        document.getElementById('today-count').textContent = "<?php echo $today_count; ?>";
        document.getElementById('rooms-count').textContent = "<?php echo count($unique_rooms); ?>";
        
        // Fonction pour basculer les détails
        function toggleDetails(id) {
            const details = document.getElementById(id);
            if (details.style.height === "" || details.style.height === "0px") {
                details.style.padding = "1rem 0";
                details.style.height = details.scrollHeight + "px";
                details.classList.remove("opacity-0");
                details.classList.add("opacity-100");
            } else {
                details.style.height = "0px";
                details.style.padding = "0";
                details.classList.remove("opacity-100");
                details.classList.add("opacity-0");
            }
        }
        
        // Mise à jour de l'horloge
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('clock').textContent = timeString;
        }
        
        // Mise à jour de la date de dernière mise à jour
        function updateLastUpdate() {
            const now = new Date();
            const options = { 
                day: 'numeric', 
                month: 'long', 
                hour: '2-digit', 
                minute: '2-digit' 
            };
            document.getElementById('last-update').textContent = now.toLocaleDateString('fr-FR', options);
        }
        
        // Filtrage des réservations
        function filterReservations(filter) {
            const cards = document.querySelectorAll('.reservation-card');
            let visibleCount = 0;
            
            cards.forEach(card => {
                const date = card.getAttribute('data-date');
                let shouldShow = false;
                
                if (filter === 'all') {
                    shouldShow = true;
                } else if (filter === 'today') {
                    const today = new Date().toISOString().split('T')[0];
                    shouldShow = date === today;
                } else if (filter === 'tomorrow') {
                    const tomorrow = new Date();
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    const tomorrowStr = tomorrow.toISOString().split('T')[0];
                    shouldShow = date === tomorrowStr;
                } else if (filter === 'week') {
                    const today = new Date();
                    const nextWeek = new Date();
                    nextWeek.setDate(today.getDate() + 7);
                    
                    const cardDate = new Date(date);
                    shouldShow = cardDate >= today && cardDate <= nextWeek;
                }
                
                if (shouldShow) {
                    card.classList.remove('hidden');
                    visibleCount++;
                } else {
                    card.classList.add('hidden');
                }
            });
            
            // Afficher/masquer le message "aucun résultat"
            const noResults = document.getElementById('no-results');
            if (visibleCount === 0) {
                noResults.classList.remove('hidden');
            } else {
                noResults.classList.add('hidden');
            }
            
            return visibleCount;
        }
        
        // Initialiser les filtres
        document.getElementById('btn-today').addEventListener('click', () => {
            const count = filterReservations('today');
            updateActiveButton('btn-today');
        });
        
        document.getElementById('btn-tomorrow').addEventListener('click', () => {
            const count = filterReservations('tomorrow');
            updateActiveButton('btn-tomorrow');
        });
        
        document.getElementById('btn-week').addEventListener('click', () => {
            const count = filterReservations('week');
            updateActiveButton('btn-week');
        });
        
        document.getElementById('btn-all').addEventListener('click', () => {
            filterReservations('all');
            updateActiveButton('btn-all');
        });
        
        document.getElementById('reset-filter').addEventListener('click', () => {
            filterReservations('all');
            updateActiveButton('btn-all');
        });
        
        function updateActiveButton(activeId) {
            const buttons = ['btn-today', 'btn-tomorrow', 'btn-week', 'btn-all'];
            buttons.forEach(id => {
                const button = document.getElementById(id);
                if (id === activeId) {
                    button.classList.remove('bg-indigo-100', 'text-indigo-800', 'hover:bg-indigo-200');
                    button.classList.add('bg-indigo-500', 'text-white', 'hover:bg-indigo-600');
                } else {
                    button.classList.remove('bg-indigo-500', 'text-white', 'hover:bg-indigo-600');
                    button.classList.add('bg-indigo-100', 'text-indigo-800', 'hover:bg-indigo-200');
                }
            });
        }
        
        // Rotation des annonces
        const announcements = [
            "Planning des réservations de salles",
            "Consultez les disponibilités des salles",
            "Réservations classées par ordre chronologique"
        ];
        let currentAnnouncement = 0;
        
        function rotateAnnouncements() {
            document.getElementById('announcement').textContent = announcements[currentAnnouncement];
            currentAnnouncement = (currentAnnouncement + 1) % announcements.length;
        }
        
        // Initialisation et mise à jour périodique
        updateClock();
        updateLastUpdate();
        setInterval(updateClock, 1000);
        setInterval(rotateAnnouncements, 8000);
    </script>
</body>
</html>
