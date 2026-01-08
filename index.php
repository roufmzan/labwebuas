<?php
// Start session
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: modules/auth/login.php");
    exit;
}

if (($_SESSION["role"] ?? '') !== 'admin' || ($_SESSION["username"] ?? '') !== 'admin') {
    header("location: user/shop.php");
    exit;
}

// Koneksi dan query data barang
require_once 'koneksi.php';

$result = null;
$sql = 'SELECT * FROM data_barang';
if (isset($conn)) {
    $result = mysqli_query($conn, $sql);
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Barang - Lab 8 PHP Database</title>
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
                <li><a href="modules/auth/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main">
        <div class="container">
            <div class="header">
                <h1 class="page-title">Daftar Barang</h1>
                <a href="tambah.php" class="btn">
                    <i class="fas fa-plus"></i> Tambah Barang Baru
                </a>
            </div>

            <?php if(isset($_GET['status'])): ?>
                <?php if($_GET['status'] == 'sukses'): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        Data berhasil disimpan!
                    </div>
                <?php elseif($_GET['status'] == 'gagal'): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        Terjadi kesalahan saat menyimpan data.
                    </div>
                <?php elseif($_GET['status'] == 'terhapus'): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-trash-alt"></i>
                        Data berhasil dihapus!
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Gambar</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result && mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_array($result)): ?>
                            <tr>
                                <td>
                                    <?php if(!empty($row['gambar'])): ?>
                                        <?php
                                            $img = $row['gambar'];
                                            $imgSrc = (strpos($img, '/') !== false) ? $img : ('gambar/' . $img);
                                        ?>
                                        <img src="<?= htmlspecialchars($imgSrc); ?>" alt="<?= htmlspecialchars($row['nama']); ?>" class="product-img">
                                    <?php else: ?>
                                        <div style="width: 60px; height: 60px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 6px;">
                                            <i class="fas fa-image" style="color: #aaa;"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['nama']); ?></td>
                                <td><?= htmlspecialchars($row['kategori']); ?></td>
                                <td>Rp <?= number_format($row['harga_beli'], 0, ',', '.'); ?></td>
                                <td>Rp <?= number_format($row['harga_jual'], 0, ',', '.'); ?></td>
                                <td><span class="badge" style="background: #e3f2fd; color: #1976d2; padding: 0.3rem 0.6rem; border-radius: 50px; font-weight: 500;"><?= $row['stok']; ?></span></td>
                                <td class="action-buttons">
                                    <a href="ubah.php?id=<?= $row['id_barang']; ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Ubah
                                    </a>
                                    <a href="hapus.php?id=<?= $row['id_barang']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                        <i class="fas fa-trash-alt"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 3rem 1rem;">
                                    <div class="empty-state">
                                        <i class="fas fa-box-open"></i>
                                        <h3>Belum ada data barang</h3>
                                        <p>Mulai dengan menambahkan barang baru</p>
                                        <a href="tambah.php" class="btn" style="margin-top: 1rem;">
                                            <i class="fas fa-plus"></i> Tambah Barang
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer style="background: var(--dark-color); color: white; padding: 2rem 0; margin-top: 3rem; text-align: center;">
        <div class="container">
            <p>&copy; <?= date('Y'); ?> Dwi Okta Ramadhani Lab 8 PHP Database</p>
        </div>
    </footer>

    <script src="assets/main.js"></script>
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Add animation to table rows
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(20px)';
                row.style.transition = `opacity 0.3s ease ${index * 0.1}s, transform 0.3s ease ${index * 0.1}s`;
                
                // Trigger reflow
                void row.offsetWidth;
                
                // Add visible class
                row.style.opacity = '1';
                row.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>