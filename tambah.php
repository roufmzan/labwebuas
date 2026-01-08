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

error_reporting(E_ALL);
include_once 'koneksi.php';
if (isset($_POST['submit']))
{
    $nama = $_POST['nama'];
    $kategori = $_POST['kategori'];
    $harga_jual = $_POST['harga_jual'];
    $harga_beli = $_POST['harga_beli'];
    $stok = $_POST['stok'];
    $file_gambar = $_FILES['file_gambar'];
    $gambar = null;
    if ($file_gambar['error'] == 0)
    {
        $filename = str_replace(' ', '_',$file_gambar['name']);
        $destination = dirname(__FILE__) .'/gambar/' . $filename;
        if(move_uploaded_file($file_gambar['tmp_name'], $destination))
        {
            $gambar = $filename;
        }
    }
    $sql = 'INSERT INTO data_barang (nama, kategori,harga_jual, harga_beli,
stok, gambar) ';
    $sql .= "VALUE ('{$nama}', '{$kategori}','{$harga_jual}',
'{$harga_beli}', '{$stok}', '{$gambar}')";
    $result = mysqli_query($conn, $sql);
    header('location: index.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Barang</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <nav>
        <div class="container nav-container">
            <a href="index.php" class="logo">
                <i class="fas fa-database"></i>
                Manajemen Barang
            </a>
            <ul class="nav-links">
                <li><a href="index.php">Beranda</a></li>
                <li><a href="tambah.php">Tambah Barang</a></li>
                <li><a href="#">Tentang</a></li>
                <li><a href="modules/auth/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <main class="main">
        <div class="container">
            <div class="header">
                <h1 class="page-title">Tambah Barang</h1>
                <a href="index.php" class="btn">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

            <div class="form-container">
                <form method="post" action="tambah.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nama">Nama Barang</label>
                        <input type="text" id="nama" name="nama" required />
                    </div>

                    <div class="form-group">
                        <label for="kategori">Kategori</label>
                        <select id="kategori" name="kategori" required>
                            <option value="" selected disabled>Pilih Kategori</option>
                            <option value="Elektronik">Elektronik</option>
                            <option value="Pakaian">Pakaian</option>
                            <option value="Makanan">Makanan</option>
                            <option value="Minuman">Minuman</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="harga_jual">Harga Jual</label>
                        <input type="number" id="harga_jual" name="harga_jual" required />
                    </div>

                    <div class="form-group">
                        <label for="harga_beli">Harga Beli</label>
                        <input type="number" id="harga_beli" name="harga_beli" required />
                    </div>

                    <div class="form-group">
                        <label for="stok">Stok</label>
                        <input type="number" id="stok" name="stok" required />
                    </div>

                    <div class="form-group">
                        <label for="file_gambar">Gambar Barang</label>
                        <input type="file" id="file_gambar" name="file_gambar" accept="image/*" />
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn" name="submit" value="1">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <a href="index.php" class="btn btn-danger">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y'); ?> Manajemen Barang - Lab 8 PHP Database</p>
        </div>
    </footer>

    <script src="assets/main.js"></script>
</body>
</html>