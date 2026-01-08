<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: modules/auth/login.php");
    exit;
}

if (($_SESSION["role"] ?? '') !== 'admin' || ($_SESSION["username"] ?? '') !== 'admin') {
    header("location: user/shop.php");
    exit;
}

require_once 'koneksi.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = (int)$_GET['id'];

$gambar = null;
if ($stmt = $conn->prepare('SELECT gambar FROM data_barang WHERE id_barang = ?')) {
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $stmt->bind_result($gambar);
        $stmt->fetch();
    }
    $stmt->close();
}

if ($stmt = $conn->prepare('DELETE FROM data_barang WHERE id_barang = ?')) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
}

if (!empty($gambar)) {
    $imgPath = $gambar;
    if (strpos($imgPath, '/') === false) {
        $imgPath = 'gambar/' . $imgPath;
    }

    if (file_exists($imgPath)) {
        unlink($imgPath);
    }
}

header('Location: index.php?status=terhapus');
exit();
