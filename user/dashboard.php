<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: http://192.168.8.152/PoleInfo/interface_login.php?error=expired");
    exit;
}

$token = $_SESSION['token'];
$api_url_verify = "http:/192.168.8.152:8000/verify-token/";
$api_url_reservations = "http://192.168.8.152:8000/reservations/";

$ch = curl_init();
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_URL, $api_url_verify);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($http_code != 200 || !$response) {
    error_log("Erreur de vérification du token : HTTP $http_code - $curl_error");
    session_destroy();
    header("Location: http://192.168.8.152/PoleInfo/interface_login.php?error=expired");
    exit;
}

$data = json_decode($response, true);
if (!$data) {
    die("Erreur : Impossible de décoder le JSON.");
}

$username = $data["user"] ?? "Inconnu";

$success_message = "";
if (isset($_SESSION['info_message'])) {
    $success_message = $_SESSION['info_message'];
    unset($_SESSION['info_message']);
}

// Récupérer les réservations
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url_reservations);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
]);

$response = curl_exec($ch);
curl_close($ch);

$data_reservations = json_decode($response, true);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pôle Info - Système de Réservation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #0f172a;
            --success: #059669;
            --danger: #dc2626;
            --warning: #d97706;
            --light-bg: #f8fafc;
            --card-bg: #ffffff;
            --text-dark: #1e293b;
            --text-light: #64748b;
            --border-color: #e2e8f0;
            --radius: 0.5rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;
            background-color: var(--light-bg);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        /* Header & Navigation */
        .navbar {
            background-color: var(--secondary);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-logo {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .navbar-logo i {
            margin-right: 0.5rem;
            color: var(--primary);
        }

        .user-nav {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .user-greeting {
            color: white;
            font-size: 0.95rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            font-size: 0.95rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-outline {
            background-color: transparent;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-outline:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }

        .btn i {
            margin-right: 0.5rem;
        }

        /* Main Content */
        .main-content {
            padding: 2rem 0;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: var(--text-light);
            font-size: 1rem;
            margin-bottom: 1rem;
        }

        /* Cards */
        .card {
            background-color: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--secondary);
            display: flex;
            align-items: center;
        }

        .card-title i {
            margin-right: 0.75rem;
            color: var(--primary);
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Alert Messages */
        .alert {
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }

        .alert i {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }

        .alert-success {
            background-color: rgba(5, 150, 105, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        /* Forms */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-dark);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            font-size: 0.95rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        .time-controls {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .result {
            padding: 1rem;
            background-color: rgba(37, 99, 235, 0.05);
            border-radius: var(--radius);
            font-weight: 500;
            margin-bottom: 1.5rem;
        }

        .result span {
            font-weight: 600;
            color: var(--primary);
        }

        /* Tables */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            white-space: nowrap;
        }

        table th, table td {
            padding: 1rem;
            text-align: left;
        }

        table th {
            background-color: #f1f5f9;
            font-weight: 600;
            color: var(--secondary);
        }

        table tbody tr {
            border-bottom: 1px solid var(--border-color);
        }

        table tbody tr:last-child {
            border-bottom: none;
        }

        table tbody tr:hover {
            background-color: rgba(37, 99, 235, 0.03);
        }

        /* Status badges */
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            text-align: center;
        }

        .badge-success {
            background-color: rgba(5, 150, 105, 0.1);
            color: var(--success);
        }

        .badge-warning {
            background-color: rgba(217, 119, 6, 0.1);
            color: var(--warning);
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .time-controls {
                grid-template-columns: 1fr;
            }
            
            .navbar-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .user-nav {
                flex-direction: column;
                width: 100%;
                gap: 1rem;
            }
            
            .btn {
                width: 100%;
            }
        }

        /* Utility classes */
        .mb-0 { margin-bottom: 0; }
        .mb-1 { margin-bottom: 0.25rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .flex-between { display: flex; justify-content: space-between; align-items: center; }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container navbar-container">
            <a href="#" class="navbar-logo">
                <i class="fas fa-calendar-alt"></i>
                Pôle Info - Réservations
            </a>
            <div class="user-nav">
                <div class="user-greeting">
                    <i class="fas fa-user-circle"></i>
                    Bonjour, <?php echo htmlspecialchars($username); ?>
                </div>
                <a href="logout.php" class="btn btn-outline btn-sm">
                    <i class="fas fa-sign-out-alt"></i>
                    Se déconnecter
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <!-- Page Header -->
            <header class="page-header">
                <h1 class="page-title">Tableau de bord</h1>
                <p class="page-subtitle">Gérez vos réservations de salles et consultez le planning.</p>
            </header>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <!-- Reservation Form Card -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-plus-circle"></i>
                        Nouvelle réservation
                    </h2>
                </div>
                <div class="card-body">
                    <form action="/user/ajout_reservation.php" method="post">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="salle" class="form-label">Salle</label>
                                <input type="text" id="salle" name="salle" class="form-control" placeholder="Ex: A104" required>
                            </div>

                            <div class="form-group">
                                <label for="matiere" class="form-label">Matière</label>
                                <input type="text" id="matiere" name="matiere" class="form-control" placeholder="Ex: Mathématiques" required>
                            </div>

                            <div class="form-group">
                                <label for="classe" class="form-label">Classe/groupe</label>
                                <input type="text" id="classe" name="classe" class="form-control" placeholder="Ex: Terminale S1" required>
                            </div>

                            <div class="form-group">
                                <label for="date_reserv" class="form-label">Date de réservation</label>
                                <input type="date" id="date_reserv" name="date_reserv" class="form-control" required>
                            </div>

                            <div class="form-group full-width">
                                <label for="message" class="form-label">Informations sur le cours/activité (optionnel)</label>
                                <textarea id="message" name="message" class="form-control" placeholder="Détails supplémentaires sur le cours ou l'activité..."></textarea>
                            </div>

                            <div class="form-group full-width">
                                <div class="time-controls">
                                    <div>
                                        <label for="startTime" class="form-label">Heure de début</label>
                                        <select id="startTime" name="startTime" class="form-control" required>
                                            <option value="">Sélectionnez une heure</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="duration" class="form-label">Durée</label>
                                        <select id="duration" name="duration" class="form-control" required>
                                            <option value="">Sélectionnez une durée</option>
                                            <option value="50">50 minutes (1 heure)</option>
                                            <option value="100">1 heure 40 (2 heures)</option>
                                            <option value="150">2 heures 30 (3 heures)</option>
                                            <option value="200">3 heures 20 (4 heures)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group full-width">
                                <div id="result" class="result">
                                    Fin prévue à : <span id="endTime">--:--</span>
                                </div>
                            </div>

                            <div class="form-group full-width text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Enregistrer la réservation
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Reservations List Card -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-list"></i>
                        Mes réservations
                    </h2>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Salle</th>
                                    <th>Matière</th>
                                    <th>Professeur</th>
                                    <th>Classe</th>
                                    <th>Horaires</th>
                                    <th>Date</th>
                                    <th>Informations</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($data_reservations)): ?>
                                    <?php foreach ($data_reservations as $reservation): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($reservation['id']); ?></td>
                                            <td><?php echo htmlspecialchars($reservation['salle']); ?></td>
                                            <td><?php echo htmlspecialchars($reservation['matiere']); ?></td>
                                            <td><?php echo htmlspecialchars($reservation['prof']); ?></td>
                                            <td><?php echo htmlspecialchars($reservation['classe']); ?></td>
                                            <td><?php echo htmlspecialchars($reservation['horaire_debut']) . ' - ' . htmlspecialchars($reservation['horaire_fin']); ?></td>
                                            <td><?php echo htmlspecialchars($reservation['date']); ?></td>
                                            <td>
                                                <?php if (!empty($reservation['info'])): ?>
                                                    <?php echo htmlspecialchars($reservation['info']); ?>
                                                <?php else: ?>
                                                    <em class="text-light">Aucune information</em>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-success">Confirmée</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center">Aucune réservation trouvée. Créez votre première réservation à l'aide du formulaire ci-dessus.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const validTimeSlots = [
            "08:10", "09:00", "09:50",
            "10:05", "10:55", "11:45",
            "13:00", "13:25", "13:50",
            "14:40", "15:30",
            "15:45", "16:35", "17:25"
        ];

        function timeToMinutes(time) {
            const [hours, minutes] = time.split(':').map(Number);
            return hours * 60 + minutes;
        }

        function minutesToTime(minutes) {
            const hours = Math.floor(minutes / 60);
            const mins = minutes % 60;
            return `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`;
        }

        function findNextValidSlot(minutes) {
            let nextSlot = validTimeSlots[0];
            for (const slot of validTimeSlots) {
                const slotMinutes = timeToMinutes(slot);
                if (slotMinutes >= minutes) {
                    nextSlot = slot;
                    break;
                }
            }
            return nextSlot;
        }

        const startTimeSelect = document.getElementById('startTime');
        validTimeSlots.forEach(time => {
            const option = new Option(time, time);
            startTimeSelect.add(option);
        });

        // Set default date value to today
        document.getElementById('date_reserv').valueAsDate = new Date();

        function calculateEndTime() {
            const startTime = document.getElementById('startTime').value;
            const duration = parseInt(document.getElementById('duration').value);

            if (startTime && duration) {
                let startMinutes = timeToMinutes(startTime);
                let endMinutes = startMinutes + duration;

                // Adjust for breaks
                if (startMinutes < timeToMinutes("09:50") && endMinutes > timeToMinutes("09:50")) {
                    endMinutes += 15; // Morning break
                }

                if (startMinutes < timeToMinutes("15:30") && endMinutes > timeToMinutes("15:30")) {
                    endMinutes += 15; // Afternoon break
                }

                const endTime = findNextValidSlot(endMinutes);
                document.getElementById('endTime').textContent = endTime;
                
                // Visual feedback
                const resultElement = document.getElementById('result');
                resultElement.style.backgroundColor = 'rgba(37, 99, 235, 0.1)';
                resultElement.style.borderLeft = '3px solid var(--primary)';
            } else {
                document.getElementById('endTime').textContent = '--:--';
            }
        }
        
        document.getElementById('startTime').addEventListener('change', calculateEndTime);
        document.getElementById('duration').addEventListener('change', calculateEndTime);
    </script>
</body>
</html>
