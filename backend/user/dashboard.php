<?php
session_start();
if (!isset($_SESSION['token'])) {
	header("Location: login.php");
	exit;
}

$token = $_SESSION['token'];
$api_url_verify = "http://127.0.0.1:8000/verify-token/";

// Configuration et exécution de la requête cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url_verify);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
	"Authorization: Bearer $token",
]);

// Exécution de la requête une seule fois
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// Vérification du code HTTP
if ($http_code != 200) {
	session_destroy();
	header("Location: login.php?error=expired");
	exit;
}

// Décodage JSON une seule fois
$data = json_decode($response, true);
if (!$data) {
	die("Erreur : Impossible de décoder le JSON.");
}

// Récupération du nom d'utilisateur avec gestion d'erreur
$username = $data["user"] ?? "Inconnu";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<title>Tableau de bord</title>
</head>
<body>
	<h2>Bienvenue <?php echo htmlspecialchars($username); ?> sur votre tableau de bord</h2>
	<p>Votre token est valide.</p>
	<a href="logout.php">Se déconnecter</a>
	<br>
	<br>

	<b>Ajouter une réservation</b>
	<form action="/backend/user/ajout_reservation.php" method="post">

		<label for="salle">Salle :</label>
		<input type="text" id="salle" name="username" required><br><br>

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
		<select id="startTime" required>
			<option value="">Sélectionnez une heure</option>
		</select>
			
			<div class="form-group">
				<label for="duration">Durée (en minutes) :</label>
				<select id="duration" required>
					<option value="">Sélectionnez une durée</option>
					<option value="50">50 minutes</option>
					<option value="100">1 heure 40</option>
					<option value="150">2 heures 30</option>
					<option value="200">3 heures 20</option>
				</select>
			</div>

			<div id="result" class="result">
				Heure de fin : <span id="endTime">--:--</span>
			</div>

			<button type="submit">Enregistrer</button>
		</form>
	</div>

	<script>
		// Définir les créneaux horaires valides
		const validTimeSlots = [
			"08:10", "09:00", "09:50",  // Matin avant pause
			"10:05", "10:55", "11:45",  // Matin après pause
			"13:00", "13:25", "13:50",  // Après-midi début
			"14:40", "15:30",           // Après-midi avant pause
			"15:45", "16:35", "17:25"   // Après-midi après pause
		];

		// Convertir une heure au format "HH:MM" en minutes depuis minuit
		function timeToMinutes(time) {
			const [hours, minutes] = time.split(':').map(Number);
			return hours * 60 + minutes;
		}

		// Convertir des minutes en format "HH:MM"
		function minutesToTime(minutes) {
			const hours = Math.floor(minutes / 60);
			const mins = minutes % 60;
			return `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`;
		}

		// Trouver le prochain créneau valide
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

		// Remplir les options d'heure avec les créneaux valides
		const startTimeSelect = document.getElementById('startTime');
		validTimeSlots.forEach(time => {
			const option = new Option(time, time);
			startTimeSelect.add(option);
		});

		// Calculer l'heure de fin
		function calculateEndTime() {
			const startTime = document.getElementById('startTime').value;
			const duration = parseInt(document.getElementById('duration').value);
			
			if (startTime && duration) {
				let startMinutes = timeToMinutes(startTime);
				let endMinutes = startMinutes + duration;

				// Ajuster pour la pause du matin (9:50 - 10:05)
				if (startMinutes < timeToMinutes("09:50") && endMinutes > timeToMinutes("09:50")) {
					endMinutes += 15;
				}

				// Ajuster pour la pause de l'après-midi (15:30 - 15:45)
				if (startMinutes < timeToMinutes("15:30") && endMinutes > timeToMinutes("15:30")) {
					endMinutes += 15;
				}

				// Trouver le créneau valide le plus proche pour l'heure de fin
				const endTime = findNextValidSlot(endMinutes);
				document.getElementById('endTime').textContent = endTime;
			} else {
				document.getElementById('endTime').textContent = '--:--';
			}
		}


		// Ajouter les écouteurs d'événements
		document.getElementById('startTime').addEventListener('change', calculateEndTime);
		document.getElementById('duration').addEventListener('change', calculateEndTime);
	</script>
</body>
</html>