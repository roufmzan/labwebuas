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

include 'koneksi.php';

// Cek apakah ada parameter ID yang dikirim
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = $_GET['id'];

// Ambil data barang berdasarkan ID
$query = "SELECT * FROM data_barang WHERE id_barang = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$barang = $result->fetch_assoc();

// Jika data tidak ditemukan, redirect ke halaman utama
if (!$barang) {
    header('Location: index.php?status=not_found');
    exit();
}

// Proses form jika ada data yang dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $kategori = $_POST['kategori'];
    $harga_beli = $_POST['harga_beli'];
    $harga_jual = $_POST['harga_jual'];
    $stok = $_POST['stok'];
    
    // Handle file upload
    $gambar = $barang['gambar']; // Default ke gambar lama
    
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['file'];
        $nama_file = $file['name'];
        $file_tmp = $file['tmp_name'];
        $ext = pathinfo($nama_file, PATHINFO_EXTENSION);
        $nama_unik = uniqid() . '.' . $ext;
        $upload_dir = 'gambar/';
        
        // Buat direktori jika belum ada
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Hapus gambar lama jika ada
        if (file_exists($upload_dir . $barang['gambar'])) {
            unlink($upload_dir . $barang['gambar']);
        }
        
        // Upload file baru
        if (move_uploaded_file($file_tmp, $upload_dir . $nama_unik)) {
            $gambar = $nama_unik;
        }
    }
    
    // Update data di database
    $query = "UPDATE data_barang SET 
              nama = ?, 
              kategori = ?, 
              harga_beli = ?, 
              harga_jual = ?, 
              stok = ?, 
              gambar = ? 
              WHERE id_barang = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssdddsi", $nama, $kategori, $harga_beli, $harga_jual, $stok, $gambar, $id);
    
    if ($stmt->execute()) {
        header('Location: index.php?status=sukses_edit');
    } else {
        header('Location: index.php?status=gagal_edit');
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Data Barang - Lab 8 PHP Database</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <!-- Navigation -->
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
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main">
        <div class="container">
            <div class="header">
                <h1 class="page-title">Ubah Data Barang</h1>
                <a href="index.php" class="btn">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

            <?php if (isset($_GET['status'])): ?>
                <?php if ($_GET['status'] == 'gagal'): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        Gagal mengupdate data. Silakan coba lagi.
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="form-container">
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nama">Nama Barang</label>
                        <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($barang['nama']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="kategori">Kategori</label>
                        <select id="kategori" name="kategori" required>
                            <option value="" disabled>Pilih Kategori</option>
                            <option value="Elektronik" <?= $barang['kategori'] == 'Elektronik' ? 'selected' : ''; ?>>Elektronik</option>
                            <option value="Pakaian" <?= $barang['kategori'] == 'Pakaian' ? 'selected' : ''; ?>>Pakaian</option>
                            <option value="Makanan" <?= $barang['kategori'] == 'Makanan' ? 'selected' : ''; ?>>Makanan</option>
                            <option value="Minuman" <?= $barang['kategori'] == 'Minuman' ? 'selected' : ''; ?>>Minuman</option>
                            <option value="Lainnya" <?= $barang['kategori'] == 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="harga_beli">Harga Beli</label>
                        <input type="number" id="harga_beli" name="harga_beli" value="<?= $barang['harga_beli']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="harga_jual">Harga Jual</label>
                        <input type="number" id="harga_jual" name="harga_jual" value="<?= $barang['harga_jual']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="stok">Stok</label>
                        <input type="number" id="stok" name="stok" value="<?= $barang['stok']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="file">Gambar Barang</label>
                        <?php if (!empty($barang['gambar'])): ?>
                            <div style="margin-bottom: 10px;">
                                <img src="gambar/<?= $barang['gambar']; ?>" alt="Gambar Barang" style="max-width: 200px; margin-bottom: 10px; display: block;">
                                <small>Gambar saat ini</small>
                            </div>
                        <?php endif; ?>
                        <input type="file" id="file" name="file" accept="image/*">
                        <small class="text-muted">Biarkan kosong jika tidak ingin mengubah gambar</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn">
                            <i class="fas fa-save"></i> Simpan Perubahan
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

    <script>
        // Validasi form sebelum submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const hargaBeli = parseFloat(document.getElementById('harga_beli').value);
            const hargaJual = parseFloat(document.getElementById('harga_jual').value);
            
            if (hargaJual <= hargaBeli) {
                e.preventDefault();
                alert('Harga jual harus lebih besar dari harga beli');
                return false;
            }
            
            const stok = parseInt(document.getElementById('stok').value);
            if (stok < 0) {
                e.preventDefault();
                alert('Stok tidak boleh kurang dari 0');
                return false;
            }
        });
    </script>
</body>
</html>
