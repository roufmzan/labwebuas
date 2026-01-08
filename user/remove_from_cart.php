<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../modules/auth/login.php");
    exit;
}

if (($_SESSION["role"] ?? '') === 'admin') {
    header("location: ../index.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0 && isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    unset($_SESSION['cart'][$id]);
}

header('Location: cart.php?status=updated');
exit;
