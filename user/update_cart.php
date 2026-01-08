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

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$qtys = $_POST['qty'] ?? null;
if (!is_array($qtys)) {
    header('Location: cart.php?status=invalid');
    exit;
}

foreach ($qtys as $idStr => $qtyStr) {
    $id = (int)$idStr;
    $qty = (int)$qtyStr;

    if ($id <= 0) {
        continue;
    }

    if ($qty <= 0) {
        unset($_SESSION['cart'][$id]);
        continue;
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
        unset($_SESSION['cart'][$id]);
        continue;
    }

    if ($qty > $stok) {
        $qty = $stok;
    }

    $_SESSION['cart'][$id] = $qty;
}

header('Location: cart.php?status=updated');
exit;
