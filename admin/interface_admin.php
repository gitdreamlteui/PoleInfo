<?php
session_start();
if (!isset($_SESSION['token']) and $_SESSION['type_compte']!=1) {
    header("Location: http://192.168.8.152/interface_login.php?error=expired");
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
            color: #333;
        }
        
        .container {
            width: 1000px;
            margin: 70px auto 30px auto;
            padding: 15px;
        }
        
        .header {
            background-color: #1a4d85;
            color: white;
            padding: 10px 20px;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-sizing: border-box;
        }
        
        .header-btn {
            background-color: #ffffff;
            border: none;
            border-radius: 4px;
            color: #1a4d85;
            padding: 6px 12px;
            margin-left: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: background-color 0.2s;
            text-decoration: none;
        }
        
        .header-btn:hover {
            background-color: #f0f5ff;
        }
        
        .title-box {
            background-color: #1a4d85;
            color: white;
            padding: 10px 15px;
            margin-bottom: 15px;
            font-weight: 600;
            font-size: 16px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .panel-container {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .admin-panel {
            background-color: white;
            border-radius: 6px;
            padding: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            flex: 1;
        }
        
        .panel-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #1a4d85;
        }
        
        .resource-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .resource-panel {
            background-color: white;
            border-radius: 6px;
            padding: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        input, select {
            width: 100%;
            padding: 8px 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: 'Inter', Arial, sans-serif;
        }
        
        .btn {
            width: 100%;
            padding: 8px 0;
            border: none;
            border-radius: 4px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-bottom: 10px;
        }
        
        .btn-add {
            background-color: #1a4d85;
        }
        
        .btn-add:hover {
            background-color: #2a5d95;
        }
        
        .btn-delete {
            background-color: #1a4d85;
            margin-top: 10px;
        }
        
        .btn-delete:hover {
            background-color: #d32f2f;
        }
        
        .separator {
            height: 1px;
            background-color: #eee;
            margin: 15px 0;
        }
        
        .footer {
            text-align: center;
            font-size: 13px;
            color: #7a8999;
            margin-top: 30px;
            border-top: 1px solid #e5e9ef;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    
    <!-- Barre de navigation -->
    <div class="header">
        <div style="font-weight: 600;">Système d'information BTS - Administration</div>
        <div>
            <a href="../index.php" class="header-btn">Tableau d'accueil</a>
            <a href="../user/dashboard.php" class="header-btn">Ajout Réservation</a>
            <a href="../user/logout.php" class="header-btn">Déconnexion</a>
        </div>
    </div>
    
    <div class="container">
        <!-- Gestion des utilisateurs -->
        <div class="panel-container">
            <div class="admin-panel">
                <div class="panel-title">Ajouts d'Utilisateurs</div>
                <form>
                    <input type="text" placeholder="Prénom">
                    <input type="text" placeholder="Nom">
                    <input type="password" placeholder="Mot de passe">
                    <select name="choix_droit">
                        <option value="Administrateur">Administrateur</option>
                        <option value="Utilisateur">Utilisateur</option>
                    </select>
                    <button type="submit" class="btn btn-add">Créer Utilisateur</button>
                </form>
            </div>
            
            <div class="admin-panel">
                <div class="panel-title">Suppressions d'Utilisateurs</div>
                <form>
                    <select name="sup_user">
                        <option value=""></option>
                    </select>
                    <button type="submit" class="btn btn-delete">Supprimer Utilisateur</button>
                </form>
            </div>
        </div>
        
        <!-- Gestion des ressources -->
        <div class="title-box">
            Gestion des ressources
        </div>
        
        <div class="resource-grid">
            <!-- Gestion des Matières -->
            <div class="resource-panel">
                <div class="panel-title">Gestion des Matières</div>
                <form>
                    <input type="text" placeholder="Nom de la matière">
                    <button type="submit" class="btn btn-add">Ajouter Matière</button>
                </form>
                <div class="separator"></div>
                <form>
                    <select name="sup_matiere">
                        <option value=""></option>
                    </select>
                    <button type="submit" class="btn btn-delete">Supprimer Matière</button>
                </form>
            </div>
            
            <!-- Gestion des Créneaux -->
            <div class="resource-panel">
                <div class="panel-title">Gestion des Créneaux</div>
                <form>
                    <input type="text" placeholder="Heure">
                    <button type="submit" class="btn btn-add">Ajouter Créneau</button>
                </form>
                <div class="separator"></div>
                <form>
                    <select name="creneau">
                        <option value=""></option>
                    </select>
                    <button type="submit" class="btn btn-delete">Supprimer Créneau</button>
                </form>
            </div>
            
            <!-- Gestion des Classes -->
            <div class="resource-panel">
                <div class="panel-title">Gestion des Classes</div>
                <form>
                    <input type="text" placeholder="Nom de la classe">
                    <button type="submit" class="btn btn-add">Ajouter Classe</button>
                </form>
                <div class="separator"></div>
                <form>
                    <select name="sup_classe">
                        <option value=""></option>
                    </select>
                    <button type="submit" class="btn btn-delete">Supprimer Classe</button>
                </form>
            </div>
            
            <!-- Gestion des Salles -->
            <div class="resource-panel">
                <div class="panel-title">Gestion des Salles</div>
                <form>
                    <input type="text" placeholder="Nom ou numéro de la salle">
                    <input type="text" placeholder="Types de salles (TP-info, Cours...)">
                    <input type="number" placeholder="Capacité de la salle : 0-99">
                    <button type="submit" class="btn btn-add">Ajouter Salle</button>
                </form>
                <div class="separator"></div>
                <form>
                    <select name="sup_salle">
                        <option value=""></option>
                    </select>
                    <button type="submit" class="btn btn-delete">Supprimer Salle</button>
                </form>
            </div>
        </div>
        
        <div class="footer">
            © 2025 Système d'information BTS - Tous droits réservés
        </div>
    </div>
</body>
</html>
