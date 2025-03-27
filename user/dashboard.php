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
    <link rel="stylesheet" href="styles.css">
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

    <script src="script.js"></script>
</body>
</html>
