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
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Bienvenue <?php echo htmlspecialchars($username); ?> sur votre tableau de bord</h2>
    <p>Votre token est valide.</p>
    <a href="logout.php">Se déconnecter</a>
    <br><br>

    <?php if ($success_message): ?>
        <div style="color: green;"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <b>Ajouter une réservation</b>
    <form action="/user/ajout_reservation.php" method="post">
        <label for="salle">Salle :</label>
        <input type="text" id="salle" name="salle" required><br><br>

        <label for="matiere">Matière :</label>
        <input type="text" id="matiere" name="matiere" required><br><br>

        <label for="classe">Classe/groupe :</label>
        <input type="text" id="classe" name="classe" required><br><br>

        <label>
            Date de la réservation :
            <input type="date" name="date_reserv" />
        </label><br><br>

        <label>
            Informations sur le cours/activité (optionel)
            <br><textarea name="message" rows="4" cols="50"></textarea>
        </label><br><br>

        <label for="startTime">Heure de début :</label>
        <select id="startTime" name="startTime" required>
            <option value="">Sélectionnez une heure</option>
        </select>

        <label for="duration">Durée (en minutes) :</label>
        <select id="duration" name="duration" required>
            <option value="">Sélectionnez une durée</option>
            <option value="50">50 minutes</option>
            <option value="100">1 heure 40</option>
            <option value="150">2 heures 30</option>
            <option value="200">3 heures 20</option>
        </select>

        <div id="result" class="result">
            Heure de fin : <span id="endTime">--:--</span>
        </div>

        <button type="submit">Enregistrer</button>
    </form>

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
</body>
</html>
