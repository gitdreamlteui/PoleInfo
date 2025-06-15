<?php
// LOGOUT.PHP


require_once __DIR__ . '/../config.php';
session_start();
session_unset();
session_destroy();

header('Location: ' . getWebUrl('interface_login.php'));
exit;
?>
