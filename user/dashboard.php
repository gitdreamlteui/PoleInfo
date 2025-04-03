<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pôle Info - Système de Réservation</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        /* Ajout de styles pour réduire l'espace */
        .compact-form .form-group {
            margin-bottom: 0.5rem;
        }
        .compact-form label {
            margin-bottom: 0.25rem;
        }
        .compact-form select, .compact-form input, .compact-form textarea {
            padding-top: 0.375rem;
            padding-bottom: 0.375rem;
        }
    </style>
</head>
<body class="bg-gray-50 font-inter text-gray-800 m-0 p-0">
    <!-- Navigation Bar - plus compacte -->
    <header class="bg-primary fixed top-0 w-full py-2 px-3 shadow-md z-10">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center text-white">
                <span class="font-semibold text-xl">Pôle Info - Réservations</span>
            </div>
            <div class="flex items-center space-x-3">
                <div class="text-white flex items-center text-sm">
                    <i class="fas fa-user-circle mr-1"></i>
                    <span>Bonjour, <?php echo htmlspecialchars($username); ?></span>
                </div>
                <a href="logout.php" class="no-underline">
                    <button class="bg-white text-primary font-medium text-sm py-1 px-3 rounded hover:bg-blue-50 transition-colors flex items-center">
                        <i class="fas fa-sign-out-alt mr-1"></i>
                        Déconnexion
                    </button>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content - marges réduites -->
    <main class="container mx-auto px-3 py-3 mt-12">
        <!-- Page Header - plus compact -->
        <div class="mb-3 flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Tableau de bord</h1>
                <p class="text-gray-600 text-sm">Gestion des réservations de salles</p>
            </div>
            <!-- Date and Time Display - déplacé à droite -->
            <div class="bg-white p-2 rounded shadow-sm border border-gray-200 flex items-center text-sm">
                <div class="text-gray-600 mr-3">
                    <span class="font-medium">Aujourd'hui : </span>
                    <span><?php echo $date_jour; ?></span>
                </div>
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium text-primary mr-1">Heure :</span>
                    <span id="clock" class="clock-display font-medium bg-primary text-white px-2 py-0.5 rounded">
                        <?php echo $heure_actuelle; ?>
                    </span>
                </div>
            </div>
        </div>

        <?php if ($success_message): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-2 mb-3 rounded shadow-sm text-sm">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <p><?php echo htmlspecialchars($success_message); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Reservation Form Card - plus compact -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-4 overflow-hidden">
            <div class="bg-primary text-white p-2 font-semibold text-base rounded-t-lg flex items-center">
                <i class="fas fa-plus-circle mr-2"></i>
                Nouvelle réservation
            </div>
            <div class="p-3">
                <form action="ajout_reservation.php" method="post" class="compact-form">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
                        <div class="form-group">
                            <label for="salle" class="block text-xs font-medium text-gray-700">Salle</label>
                            <select name="salle" id="salle" class="w-full px-2 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-transparent" required>
                                <option value="">--Sélectionnez--</option>
                                <?php foreach ($salles as $salle) {
                                    echo "<option value='{$salle['numero']}'>{$salle['numero']}</option>";
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="matiere" class="block text-xs font-medium text-gray-700">Matière</label>
                            <select name="matiere" id="matiere" class="w-full px-2 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-transparent" required>
                                <option value="">--Sélectionnez--</option>
                                <option value="Informatique">Informatique</option>
                                <option value="Culture Generale & Expression">Culture générale</option>
                                <option value="Mathematiques">Mathématiques</option>
                                <option value="Physique">Physique</option>
                                <option value="Anglais">Anglais</option>
                                <option value="ESLA">ESLA</option>
                                <option value="BAS">BAS</option>
                                <option value="ACF">ACF</option>
                                <option value="Co-Physique">Co-enseignement physique</option>
                                <option value="Co-Maths">Co-enseignement maths</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="classe" class="block text-xs font-medium text-gray-700">Classe/groupe</label>
                            <select name="classe[]" id="classe" class="w-full px-2 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-transparent" multiple required>
                                <option value="CIEL1">CIEL1</option>
                                <option value="CIEL2">CIEL2</option>
                                <option value="CIAP1">CIAP1</option>
                                <option value="CIAP2">CIAP2</option>
                                <option value="CIEL1_Grp1">CIEL1_Grp1</option>
                                <option value="CIEL1_Grp2">CIEL1_Grp2</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="date_reserv" class="block text-xs font-medium text-gray-700">Date</label>
                            <input type="date" id="date_reserv" name="date_reserv" class="w-full px-2 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-transparent" required>
                        </div>
                        <div class="form-group md:col-span-2">
                            <label for="startTime" class="block text-xs font-medium text-gray-700">Heure de début</label>
                            <select id="startTime" name="startTime" class="w-full px-2 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-transparent" required>
                                <option value="">Sélectionnez</option>
                                <?php 
                                    foreach ($creneaux as $creneau) {
                                        echo "<option value='$creneau'>$creneau</option>";
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="form-group md:col-span-2">
                            <label for="duration" class="block text-xs font-medium text-gray-700">Durée</label>
                            <select id="duration" name="duration" class="w-full px-2 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-transparent" required>
                                <option value="">Sélectionnez</option>
                                <option value="0.83">50 min (1h)</option>
                                <option value="1.67">1h40 (2h)</option>
                                <option value="2.5">2h30 (3h)</option>
                                <option value="3.33">3h20 (4h)</option>
                            </select>
                        </div>
                        <div class="form-group md:col-span-4">
                            <label for="message" class="block text-xs font-medium text-gray-700">Informations (optionnel)</label>
                            <textarea id="message" name="message" class="w-full px-2 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-transparent h-16" placeholder="Détails supplémentaires sur le cours ou l'activité..."></textarea>
                        </div>
                        <div class="md:col-span-4 flex justify-end mt-2">
                            <button type="submit" class="bg-primary text-white font-medium text-sm py-1.5 px-3 rounded-md hover:bg-blue-700 transition-colors flex items-center">
                                <i class="fas fa-save mr-1"></i>
                                Enregistrer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Footer - plus compact -->
        <footer class="text-center text-xs text-gray-500 mt-4 border-t border-gray-200 pt-2">
            © 2025 Système d'information BTS - Tous droits réservés
        </footer>
    </main>
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
</body>
</html>