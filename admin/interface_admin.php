<?php
session_start();
if (!isset($_SESSION['token']) and $_SESSION['type_compte']!=1) {
    header("Location: http://127.0.0.1/interface_login.php?error=expired");
    exit;
}

$token = $_SESSION['token'];
$api_url_verify = "http:/127.0.0.1:8000/verify-token/";
$api_url_reservations = "http://127.0.0.1:8000/reservations/";

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
    header("Location: http://192.168.8.152/interface_login.php?error=expired");
    exit;
}

if ($http_code != 200) {
    session_destroy();
    header("Location: http://192.168.8.152/interface_login.php?error=expired");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestion</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    
    <header>
        <h1>Système d'information BTS - Administration</h1>
        <div>
            <a href="../index.php">Tableau d'accueil</a>
            <a href="../user/dashboard.php">Ajout Réservation</a>
            <a href="../user/logout.php">Déconnexion</a>
        </div>
    </header>
    
    <div>
        <div>
            <div>
                <h2>Ajouts d'Utilisateurs</h2>
                <form>
                    <input type="text" placeholder="Prénom">
                    <input type="text" placeholder="Nom">
                    <input type="password" placeholder="Mot de passe">
                    <select name="choix_droit">
                        <option value="Administrateur">Administrateur</option>
                        <option value="Utilisateur">Utilisateur</option>
                    </select>
                    <button type="submit">Créer Utilisateur</button>
                </form>
            </div>
            
            <div>
                <h2>Suppressions d'Utilisateurs</h2>
                <form>
                    <select name="sup_user">
                        <option value=""></option>
                    </select>
                    <button type="submit">Supprimer Utilisateur</button>
                </form>
            </div>
        </div>
        
        <div>
            Gestion des ressources
        </div>
        
        <div>
            <div>
                <h2>Gestion des Matières</h2>
                <form>
                    <input type="text" placeholder="Nom de la matière"require>
                    <button type="submit" >Ajouter Matière</button>
                </form>
                <form class="mt-10 space-y-4">
                    <select name="sup_matiere">
                        <option value=""></option>
                    </select>
                    <button type="submit">Supprimer Matière</button>
                </form>
            </div>
            
            <div>
                <h2>Gestion des Créneaux</h2>
                <form>
                    <input type="text" placeholder="00:00" require>
                    <button type="submit">Ajouter Créneau</button>
                </form>
                <form>
                    <select name="creneau">
                        <?php
                        $get_creneau = "http://192.168.8.152:8000/creneaux/";
                        $reponse_creneau = file_get_contents($get_creneau);
                        $data_creneau = json_decode($reponse_creneau, true);
                        
                        if (is_array($data_creneau)) {
                            foreach ($data_creneau as $item) {
                                $interval = new DateInterval($item['heure_debut']);
                                $heures = $interval->h;
                                $minutes = $interval->i;
                                $creneau = sprintf("%02d:%02d", $heures, $minutes);
                                echo "<option value='$creneau'>$creneau</option>";
                            }
                        }
                        ?>
                    </select>
                    <button type="submit">Supprimer Créneau</button>
                </form>
            </div>
            
            <div>
                <h2>Gestion des Classes</h2>
                <form>
                    <input type="text" placeholder="Nom de la classe" require>
                    <button type="submit">Ajouter Classe</button>
                </form>
                <form>
                    <select name="sup_classe">
                        <option value=""></option>
                    </select>
                    <button type="submit" >Supprimer Classe</button>
                </form>
            </div>
            
            <div>
                <h2>Gestion des Salles</h2>
                <form>
                    <input type="text" placeholder="Nom ou numéro de la salle"require>
                    <input type="text" placeholder="Type de salle (TP-info, Cours, etc.)" require>
                    <input type="number" placeholder="Capacité de la salle"  require>
                    <button type="submit" >Ajouter Salle</button>
                </form>
                <form>
                    <select name="sup_salle">
                        <option value=""></option>
                    </select>
                    <button type="submit">Supprimer Salle</button>
                </form>
            </div>
        </div>
    </div>
    
    <footer >
        <p>© 2025 Système d'information BTS - Tous droits réservés</p>
    </footer>
</body>
</html>
