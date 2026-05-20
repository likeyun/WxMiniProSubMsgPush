<?php
session_start();
unset($_SESSION['wepush_login']);
unset($_SESSION['wepush_user']);
header('Location: login.php');
exit;