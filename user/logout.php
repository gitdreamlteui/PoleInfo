<?php
require_once 'config.php';
session_start();
session_unset();
session_destroy();
$web_url = getWebUrl();

header('Location: ' . getWebUrl('/interface_login.php?error='));
exit;
?>