<?php
session_start(); // Il faut d'abord démarrer la session avant de la détruire
session_unset();
session_destroy();
echo "Vous êtes maintenant déconnecté"; // Ajout du point-virgule manquant
header('Location: http://127.0.0.1/user/index.html');
exit;
?>