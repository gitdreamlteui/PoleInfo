<?php
require_once 'config.php';
session_start();
session_unset();
session_destroy();

header('Location: ' . getWebUrl('/index.php'));
exit;
?>
