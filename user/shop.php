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

$cartCount = 0;
foreach ($_SESSION['cart'] as $qty) {
    $cartCount += (int)$qty;
}

$result = null;
$sql = 'SELECT id_barang, nama, kategori, harga_jual, stok, gambar FROM data_barang ORDER BY id_barang DESC';
$result = mysqli_query($conn, $sql);

$status = $_GET['status'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Belanja Barang</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <nav>
        <div class="container nav-container">
            <a href="shop.php" class="logo">
                <i class="fas fa-store"></i>
                Belanja Barang
            </a>
            <ul class="nav-links">
                <li><a href="shop.php">Katalog</a></li>
                <li><a href="cart.php">Keranjang (<?= $cartCount; ?>)</a></li>
                <li><a href="../modules/auth/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <main class="main">
        <div class="container">
            <div class="header">
                <h1 class="page-title">Katalog Barang</h1>
                <a href="cart.php" class="btn">
                    <i class="fas fa-shopping-cart"></i> Lihat Keranjang
                </a>
            </div>

            <?php if ($status === 'checkout_success'): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Checkout berhasil!
                </div>
            <?php elseif ($status === 'checkout_failed'): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    Checkout gagal. Stok tidak mencukupi atau terjadi kesalahan.
                </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1rem;">
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <?php
                            $img = $row['gambar'] ?? '';
                            $imgSrc = '';
                            if (!empty($img)) {
                                $imgSrc = (strpos($img, '/') !== false) ? ('../' . $img) : ('../gambar/' . $img);
                            }
                            $stok = (int)$row['stok'];
                        ?>
                        <div style="background: white; border-radius: 10px; box-shadow: 0 2px 15px rgba(0,0,0,0.05); overflow: hidden;">
                            <div style="height: 160px; background: #f0f0f0; display:flex; align-items:center; justify-content:center;">
                                <?php if (!empty($imgSrc)): ?>
                                    <img src="<?= htmlspecialchars($imgSrc); ?>" alt="<?= htmlspecialchars($row['nama']); ?>" style="width:100%; height:100%; object-fit:cover;">
                                <?php else: ?>
                                    <i class="fas fa-image" style="color:#aaa; font-size:2rem;"></i>
                                <?php endif; ?>
                            </div>
                            <div style="padding: 1rem;">
                                <div style="font-weight: 600; margin-bottom: 0.25rem;"><?= htmlspecialchars($row['nama']); ?></div>
                                <div style="color:#6c757d; font-size: 0.9rem; margin-bottom: 0.5rem;">Kategori: <?= htmlspecialchars($row['kategori']); ?></div>
                                <div style="font-weight: 700; margin-bottom: 0.5rem;">Rp <?= number_format((float)$row['harga_jual'], 0, ',', '.'); ?></div>
                                <div style="margin-bottom: 0.75rem;">
                                    <span class="badge" style="background: #e3f2fd; color: #1976d2;">Stok: <?= $stok; ?></span>
                                </div>

                                <form method="post" action="add_to_cart.php" style="display:flex; gap: 0.5rem; align-items: center;">
                                    <input type="hidden" name="id" value="<?= (int)$row['id_barang']; ?>">
                                    <input type="number" name="qty" min="1" max="<?= max(1, $stok); ?>" value="1" style="margin-bottom:0; width: 90px;" <?= $stok <= 0 ? 'disabled' : ''; ?> />
                                    <button type="submit" class="btn" style="padding: 0.55rem 1rem;" <?= $stok <= 0 ? 'disabled' : ''; ?>>
                                        <i class="fas fa-cart-plus"></i> Tambah
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <i class="fas fa-box-open"></i>
                        <h3>Belum ada barang</h3>
                        <p>Silakan hubungi admin untuk menambahkan barang.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y'); ?> Belanja Barang</p>
        </div>
    </footer>

    <script src="../assets/main.js"></script>
</body>
</html>
