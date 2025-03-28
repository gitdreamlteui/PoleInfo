<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affichage - Système d'information BTS</title>
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
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col">
    <!-- Entête fixe -->
    <header class="bg-indigo-900 text-white shadow-lg sticky top-0 z-10">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <i class="fas fa-graduation-cap text-2xl text-indigo-300"></i>
                <h1 class="text-xl font-bold tracking-wide">Système d'Information BTS</h1>
            </div>
            <div class="time-display text-xl font-semibold" id="clock">00:00:00</div>
        </div>
    </header>

    <!-- Bannière d'information -->
    <div class="bg-indigo-600 text-white px-4 py-3 text-center text-lg font-medium shadow">
        <div class="container mx-auto animate-pulse">
            <i class="fas fa-bullhorn mr-2"></i> 
            <span id="announcement">Informations importantes affichées ici</span>
        </div>
    </div>

    <!-- Contenu principal -->
    <main class="flex-1 container mx-auto p-4 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Section Planning du jour -->
        <section class="bg-white rounded-lg shadow-md overflow-hidden h-full flex flex-col">
            <div class="section-header text-white p-4 flex items-center">
                <i class="fas fa-calendar-day text-xl mr-3"></i>
                <h2 class="text-xl font-bold">Planning du jour</h2>
            </div>
            <div class="p-4 flex-1 overflow-y-auto">
                <div class="text-center text-xl font-semibold mb-4 bg-indigo-50 py-2 rounded border border-indigo-100">
                    <i class="far fa-calendar-alt mr-2 text-indigo-500"></i>
                    <span id="current-date">Lundi 1er Janvier 2025</span>
                </div>
                <div class="space-y-3">
                    <!-- Liste des cours/événements -->
                    <div class="border-l-4 border-indigo-500 pl-4 py-2 bg-indigo-50 rounded-r-md">
                        <p class="font-semibold text-indigo-900">08:00 - 09:30</p>
                        <h3 class="font-bold text-lg">Mathématiques Appliquées</h3>
                        <div class="flex justify-between text-indigo-700">
                            <span><i class="fas fa-chalkboard-teacher mr-1"></i> M. DUPONT</span>
                            <span><i class="fas fa-map-marker-alt mr-1"></i> Salle B204</span>
                        </div>
                    </div>
                    
                    <div class="border-l-4 border-indigo-500 pl-4 py-2 bg-indigo-50 rounded-r-md">
                        <p class="font-semibold text-indigo-900">09:45 - 11:15</p>
                        <h3 class="font-bold text-lg">Anglais Technique</h3>
                        <div class="flex justify-between text-indigo-700">
                            <span><i class="fas fa-chalkboard-teacher mr-1"></i> Mme SMITH</span>
                            <span><i class="fas fa-map-marker-alt mr-1"></i> Salle C103</span>
                        </div>
                    </div>
                    
                    <div class="border-l-4 border-yellow-500 pl-4 py-2 bg-yellow-50 rounded-r-md">
                        <p class="font-semibold text-yellow-800">11:15 - 13:00</p>
                        <h3 class="font-bold text-lg">PAUSE DÉJEUNER</h3>
                    </div>
                    
                    <div class="border-l-4 border-indigo-500 pl-4 py-2 bg-indigo-50 rounded-r-md">
                        <p class="font-semibold text-indigo-900">13:00 - 16:30</p>
                        <h3 class="font-bold text-lg">Projet Informatique</h3>
                        <div class="flex justify-between text-indigo-700">
                            <span><i class="fas fa-chalkboard-teacher mr-1"></i> M. MARTIN</span>
                            <span><i class="fas fa-map-marker-alt mr-1"></i> Labo Info</span>
                        </div>
                        <p class="mt-1 text-gray-600 text-sm">
                            N'oubliez pas d'apporter vos rapports d'avancement pour l'évaluation intermédiaire.
                        </p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Section Informations -->
        <section class="bg-white rounded-lg shadow-md overflow-hidden h-full flex flex-col">
            <div class="section-header text-white p-4 flex items-center">
                <i class="fas fa-info-circle text-xl mr-3"></i>
                <h2 class="text-xl font-bold">Informations importantes</h2>
            </div>
            <div class="p-4 flex-1 overflow-y-auto space-y-4">
                <!-- Alertes -->
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 text-2xl mr-2"></i>
                        <h3 class="font-bold text-red-700">Alerte</h3>
                    </div>
                    <p class="mt-1 text-red-700">Le bâtiment C sera fermé le jeudi 15 janvier pour maintenance. Les cours prévus sont déplacés au bâtiment A.</p>
                </div>
                
                <!-- Annonces -->
                <div class="info-card bg-indigo-50 rounded-lg p-4 border border-indigo-200">
                    <h3 class="font-bold text-lg text-indigo-800 flex items-center">
                        <i class="fas fa-newspaper mr-2"></i> 
                        Session examens blancs
                    </h3>
                    <p class="mt-2">Les examens blancs auront lieu du 5 au 9 février. Le planning détaillé sera disponible à partir du 25 janvier sur l'ENT.</p>
                    <p class="text-sm text-indigo-600 mt-1 font-medium">Publié le: 10/01/2025</p>
                </div>
                
                <div class="info-card bg-indigo-50 rounded-lg p-4 border border-indigo-200">
                    <h3 class="font-bold text-lg text-indigo-800 flex items-center">
                        <i class="fas fa-users mr-2"></i> 
                        Forum entreprises BTS
                    </h3>
                    <p class="mt-2">Le forum annuel des entreprises partenaires se tiendra le mercredi 22 janvier de 14h à 18h dans le hall principal. Présence obligatoire pour les étudiants de 2ème année.</p>
                    <p class="text-sm text-indigo-600 mt-1 font-medium">Publié le: 08/01/2025</p>
                </div>
                
                <div class="info-card bg-green-50 rounded-lg p-4 border border-green-200">
                    <h3 class="font-bold text-lg text-green-800 flex items-center">
                        <i class="fas fa-laptop-code mr-2"></i> 
                        Nouveaux ordinateurs disponibles
                    </h3>
                    <p class="mt-2">Le laboratoire informatique a été équipé de 20 nouveaux postes de travail. Les réservations pour les TP sont désormais ouvertes via l'application de réservation.</p>
                    <p class="text-sm text-green-600 mt-1 font-medium">Publié le: 05/01/2025</p>
                </div>
            </div>
        </section>
        
        <!-- Section Salles libres -->
        <section class="bg-white rounded-lg shadow-md overflow-hidden h-full flex flex-col">
            <div class="section-header text-white p-4 flex items-center">
                <i class="fas fa-door-open text-xl mr-3"></i>
                <h2 class="text-xl font-bold">Salles disponibles</h2>
            </div>
            <div class="p-4 flex-1 overflow-y-auto">
                <div class="mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="font-semibold text-gray-700">Disponibilité immédiate</h3>
                        <span class="text-indigo-600 font-medium text-sm">Mise à jour il y a 5 min</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div class="text-center p-3 bg-green-100 rounded-lg border border-green-200 hover:bg-green-200 transition-colors">
                            <i class="fas fa-laptop text-green-600 text-lg mb-1"></i>
                            <p class="font-bold">A101</p>
                            <p class="text-sm text-green-700">Informatique</p>
                        </div>
                        <div class="text-center p-3 bg-green-100 rounded-lg border border-green-200 hover:bg-green-200 transition-colors">
                            <i class="fas fa-book text-green-600 text-lg mb-1"></i>
                            <p class="font-bold">B204</p>
                            <p class="text-sm text-green-700">Cours standard</p>
                        </div>
                        <div class="text-center p-3 bg-green-100 rounded-lg border border-green-200 hover:bg-green-200 transition-colors">
                            <i class="fas fa-flask text-green-600 text-lg mb-1"></i>
                            <p class="font-bold">C301</p>
                            <p class="text-sm text-green-700">Laboratoire</p>
                        </div>
                        <div class="text-center p-3 bg-green-100 rounded-lg border border-green-200 hover:bg-green-200 transition-colors">
                            <i class="fas fa-book text-green-600 text-lg mb-1"></i>
                            <p class="font-bold">D105</p>
                            <p class="text-sm text-green-700">Cours standard</p>
                        </div>
                        <div class="text-center p-3 bg-green-100 rounded-lg border border-green-200 hover:bg-green-200 transition-colors">
                            <i class="fas fa-users text-green-600 text-lg mb-1"></i>
                            <p class="font-bold">E201</p>
                            <p class="text-sm text-green-700">Réunion</p>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h3 class="font-semibold text-gray-700 mb-2">À partir de 13h00</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div class="text-center p-3 bg-blue-50 rounded-lg border border-blue-200 hover:bg-blue-100 transition-colors">
                            <i class="fas fa-chalkboard text-blue-600 text-lg mb-1"></i>
                            <p class="font-bold">A103</p>
                            <p class="text-sm text-blue-700">Salle TD</p>
                        </div>
                        <div class="text-center p-3 bg-blue-50 rounded-lg border border-blue-200 hover:bg-blue-100 transition-colors">
                            <i class="fas fa-laptop text-blue-600 text-lg mb-1"></i>
                            <p class="font-bold">B102</p>
                            <p class="text-sm text-blue-700">Informatique</p>
                        </div>
                        <div class="text-center p-3 bg-blue-50 rounded-lg border border-blue-200 hover:bg-blue-100 transition-colors">
                            <i class="fas fa-book text-blue-600 text-lg mb-1"></i>
                            <p class="font-bold">D201</p>
                            <p class="text-sm text-blue-700">Cours standard</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Section Actualités et événements -->
        <section class="bg-white rounded-lg shadow-md overflow-hidden h-full flex flex-col">
            <div class="section-header text-white p-4 flex items-center">
                <i class="fas fa-rss text-xl mr-3"></i>
                <h2 class="text-xl font-bold">Actualités et événements</h2>
            </div>
            <div class="p-4 flex-1 overflow-y-auto space-y-4">
                <div class="flex gap-4 border-b border-gray-200 pb-4">
                    <div class="flex-shrink-0 w-20 h-20 rounded-md bg-indigo-200 flex items-center justify-center">
                        <span class="font-bold text-indigo-700 text-xl">22<br/>JAN</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-indigo-800">Forum entreprises 2025</h3>
                        <p class="text-gray-600 text-sm">Rencontrez nos partenaires professionnels et découvrez les opportunités de stages et d'alternance.</p>
                        <p class="text-indigo-600 font-medium text-xs mt-1">14h00 - 18h00 | Hall principal</p>
                    </div>
                </div>
                
                <div class="flex gap-4 border-b border-gray-200 pb-4">
                    <div class="flex-shrink-0 w-20 h-20 rounded-md bg-indigo-200 flex items-center justify-center">
                        <span class="font-bold text-indigo-700 text-xl">05<br/>FÉV</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-indigo-800">Début des examens blancs</h3>
                        <p class="text-gray-600 text-sm">Session de préparation aux examens officiels. Consultez le planning détaillé sur l'ENT.</p>
                        <p class="text-indigo-600 font-medium text-xs mt-1">Tous les jours | Selon planning</p>
                    </div>
                </div>
                
                <div class="flex gap-4 border-b border-gray-200 pb-4">
                    <div class="flex-shrink-0 w-20 h-20 rounded-md bg-indigo-200 flex items-center justify-center">
                        <span class="font-bold text-indigo-700 text-xl">15<br/>FÉV</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-indigo-800">Conférence Intelligence Artificielle</h3>
                        <p class="text-gray-600 text-sm">Conférence animée par M. BERNARD, expert en IA chez DataTech. Ouvert à tous les étudiants.</p>
                        <p class="text-indigo-600 font-medium text-xs mt-1">14h30 - 16h30 | Amphithéâtre</p>
                    </div>
                </div>
                
                <div class="flex gap-4">
                    <div class="flex-shrink-0 w-20 h-20 rounded-md bg-indigo-200 flex items-center justify-center">
                        <span class="font-bold text-indigo-700 text-xl">01<br/>MAR</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-indigo-800">Journée Portes Ouvertes</h3>
                        <p class="text-gray-600 text-sm">Présentation de notre établissement aux futurs étudiants. Les volontaires pour l'accueil sont les bienvenus.</p>
                        <p class="text-indigo-600 font-medium text-xs mt-1">09h00 - 17h00 | Tout l'établissement</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Pied de page -->
    <footer class="bg-indigo-900 text-white py-3 mt-6">
        <div class="container mx-auto px-4 text-center text-sm">
            <p>© 2025 Système d'information BTS - Tous droits réservés</p>
            <p class="mt-1 text-indigo-300">Un problème d'affichage ? Contactez le service informatique au poste 4215</p>
        </div>
    </footer>

    <script>
        // Horloge en temps réel
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('fr-FR', { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit'
            });
            document.getElementById('clock').textContent = timeString;
        }

        // Date formatée
        function updateDate() {
            const now = new Date();
            const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
            const dateString = now.toLocaleDateString('fr-FR', options);
            document.getElementById('current-date').textContent = dateString.charAt(0).toUpperCase() + dateString.slice(1);
        }

        // Rotation des annonces
        const announcements = [
            "Réunion pédagogique le 20 janvier - Fin des cours à 15h30",
            "Les inscriptions aux certifications TOEIC sont ouvertes jusqu'au 25 janvier",
            "Rappel : Remise des rapports de stage avant le 31 janvier"
        ];
        let currentAnnouncement = 0;

        function rotateAnnouncements() {
            document.getElementById('announcement').textContent = announcements[currentAnnouncement];
            currentAnnouncement = (currentAnnouncement + 1) % announcements.length;
        }

        // Initialisation et mise à jour périodique
        updateClock();
        updateDate();
        rotateAnnouncements();
        setInterval(updateClock, 1000);
        setInterval(rotateAnnouncements, 8000);

        // Simuler des mises à jour d'information
        setInterval(() => {
            const infoCards = document.querySelectorAll('.info-card');
            const randomCard = infoCards[Math.floor(Math.random() * infoCards.length)];
            randomCard.classList.add('info-update');
            setTimeout(() => randomCard.classList.remove('info-update'), 2000);
        }, 30000);
    </script>
</body>
</html>
