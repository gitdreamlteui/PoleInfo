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

if ($http_code != 200) {
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
    $success_message = "Réservation ajoutée avec succès!";
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
    <title>Tableau de bord</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f7fafc;
            color: #2d3748;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background-color: #4f46e5; /* indigo-500 */
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #4f46e5; /* indigo-500 */
            margin-top: 30px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 10px;
        }

        .logout-link {
            background-color: #e5e7eb;
            color: #1e40af;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .logout-link:hover {
            background-color: #d1d5db;
        }

        .success-message {
            background-color: #ecfdf5;
            color: #065f46;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #10b981;
        }

        .form-container {
            background-color: white;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }

        form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #4b5563;
        }

        form input[type="text"],
        form input[type="date"],
        form textarea,
        form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 16px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 14px;
        }

        form textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        button {
            background-color: #4f46e5; /* indigo-500 */
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        button:hover {
            background-color: #4338ca; /* indigo-600 */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #4f46e5; /* indigo-500 */
            color: white;
            font-weight: 500;
        }

        tr:nth-child(even) {
            background-color: #f3f4f6;
        }

        td {
            border-bottom: 1px solid #e5e7eb;
        }

        .result {
            margin-top: 12px;
            margin-bottom: 20px;
            padding: 8px;
            background-color: #eef2ff; /* indigo-50 */
            border-radius: 4px;
            border-left: 3px solid #4f46e5;
        }

        .time-controls {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
        }

        .time-controls select {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h2 style="color: white; border-bottom: none; margin-top: 0;">Bienvenue <?php echo htmlspecialchars($username); ?> sur votre tableau de bord</h2>
            <p>Votre token est valide.</p>
            <a href="logout.php" class="logout-link">Se déconnecter</a>
        </header>

        <?php if ($success_message): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <div class="form-container">
            <h2>Ajouter une réservation</h2>
            <form action="/user/ajout_reservation.php" method="post">
                <div class="form-group">
                    <label for="salle">Salle :</label>
                    <input type="text" id="salle" name="salle" required>
                </div>

                <div class="form-group">
                    <label for="matiere">Matière :</label>
                    <input type="text" id="matiere" name="matiere" required>
                </div>

                <div class="form-group">
                    <label for="classe">Classe/groupe :</label>
                    <input type="text" id="classe" name="classe" required>
                </div>

                <div class="form-group">
                    <label for="date_reserv">Date de la réservation :</label>
                    <input type="date" id="date_reserv" name="date_reserv" required>
                </div>

                <div class="form-group">
                    <label for="message">Informations sur le cours/activité (optionel) :</label>
                    <textarea id="message" name="message" rows="4"></textarea>
                </div>

                <div class="time-controls">
                    <div class="form-group">
                        <label for="startTime">Heure de début :</label>
                        <select id="startTime" name="startTime" required>
                            <option value="">Sélectionnez une heure</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="duration">Durée (en minutes) :</label>
                        <select id="duration" name="duration" required>
                            <option value="">Sélectionnez une durée</option>
                            <option value="50">50 minutes</option>
                            <option value="100">1 heure 40</option>
                            <option value="150">2 heures 30</option>
                            <option value="200">3 heures 20</option>
                        </select>
                    </div>
                </div>

                <div id="result" class="result">
                    Heure de fin : <span id="endTime">--:--</span>
                </div>

                <button type="submit">Enregistrer</button>
            </form>
        </div>

        <h2>Liste des Réservations</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Salle</th>
                    <th>Matière</th>
                    <th>Professeur</th>
                    <th>Classe</th>
                    <th>Horaire Début</th>
                    <th>Horaire Fin</th>
                    <th>Date</th>
                    <th>Info</th>
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
                            <td><?php echo htmlspecialchars($reservation['horaire_debut']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['horaire_fin']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['date']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['info']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">Aucune réservation trouvée.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
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

        function calculateEndTime() {
            const startTime = document.getElementById('startTime').value;
            const duration = parseInt(document.getElementById('duration').value);

            if (startTime && duration) {
                let startMinutes = timeToMinutes(startTime);
                let endMinutes = startMinutes + duration;

                if (startMinutes < timeToMinutes("09:50") && endMinutes > timeToMinutes("09:50")) {
                    endMinutes += 15;
                }

                if (startMinutes < timeToMinutes("15:30") && endMinutes > timeToMinutes("15:30")) {
                    endMinutes += 15;
                }

                const endTime = findNextValidSlot(endMinutes);
                document.getElementById('endTime').textContent = endTime;
            } else {
                document.getElementById('endTime').textContent = '--:--';
            }
        }
        document.getElementById('startTime').addEventListener('change', calculateEndTime);
        document.getElementById('duration').addEventListener('change', calculateEndTime);
    </script>
</body>
</html>
