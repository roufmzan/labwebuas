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

require_once '../koneksi.php';

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
if ($id <= 0) {
    header('Location: shop.php');
    exit;
}

if ($qty < 1) {
    $qty = 1;
}

$stok = 0;
if ($stmt = $conn->prepare('SELECT stok FROM data_barang WHERE id_barang = ?')) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($stok);
    $stmt->fetch();
    $stmt->close();
}

$stok = (int)$stok;
if ($stok <= 0) {
    header('Location: shop.php');
    exit;
}

if ($qty > $stok) {
    $qty = $stok;
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$current = isset($_SESSION['cart'][$id]) ? (int)$_SESSION['cart'][$id] : 0;
$newQty = $current + $qty;
if ($newQty > $stok) {
    $newQty = $stok;
}

$_SESSION['cart'][$id] = $newQty;

header('Location: cart.php');
exit;
