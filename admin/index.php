<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../modules/auth/login.php");
    exit;
}

if (($_SESSION["role"] ?? '') !== 'admin' || ($_SESSION["username"] ?? '') !== 'admin') {
    header("location: ../user/shop.php");
    exit;
}

header("location: ../index.php");
exit;
