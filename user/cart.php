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

$cart = $_SESSION['cart'];
$ids = array_keys($cart);

$items = [];
$total = 0;

if (count($ids) > 0) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $sql = "SELECT id_barang, nama, kategori, harga_jual, stok, gambar FROM data_barang WHERE id_barang IN ($placeholders)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $id = (int)$row['id_barang'];
        $qty = isset($cart[$id]) ? (int)$cart[$id] : 0;
        if ($qty <= 0) {
            continue;
        }
        $row['qty'] = $qty;
        $row['subtotal'] = (float)$row['harga_jual'] * $qty;
        $items[] = $row;
        $total += $row['subtotal'];
    }

    $stmt->close();
}

$cartCount = 0;
foreach ($_SESSION['cart'] as $qty) {
    $cartCount += (int)$qty;
}

$status = $_GET['status'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang</title>
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
                <h1 class="page-title">Keranjang</h1>
                <a href="shop.php" class="btn">
                    <i class="fas fa-arrow-left"></i> Lanjut Belanja
                </a>
            </div>

            <?php if ($status === 'updated'): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Keranjang diperbarui.
                </div>
            <?php elseif ($status === 'invalid'): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    Terjadi kesalahan saat memperbarui keranjang.
                </div>
            <?php endif; ?>

            <?php if (count($items) === 0): ?>
                <div class="empty-state">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Keranjang masih kosong</h3>
                    <p>Tambahkan barang dari katalog.</p>
                    <a href="shop.php" class="btn" style="margin-top: 1rem;">
                        <i class="fas fa-store"></i> Ke Katalog
                    </a>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <form method="post" action="update_cart.php">
                        <table>
                            <thead>
                                <tr>
                                    <th>Gambar</th>
                                    <th>Barang</th>
                                    <th>Harga</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $row): ?>
                                    <?php
                                        $img = $row['gambar'] ?? '';
                                        $imgSrc = '';
                                        if (!empty($img)) {
                                            $imgSrc = (strpos($img, '/') !== false) ? ('../' . $img) : ('../gambar/' . $img);
                                        }
                                    ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($imgSrc)): ?>
                                                <img src="<?= htmlspecialchars($imgSrc); ?>" class="product-img" alt="<?= htmlspecialchars($row['nama']); ?>">
                                            <?php else: ?>
                                                <div style="width: 60px; height: 60px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 6px;">
                                                    <i class="fas fa-image" style="color: #aaa;"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600;"><?= htmlspecialchars($row['nama']); ?></div>
                                            <div style="color:#6c757d; font-size:0.9rem;">Kategori: <?= htmlspecialchars($row['kategori']); ?></div>
                                            <div style="color:#6c757d; font-size:0.9rem;">Stok: <?= (int)$row['stok']; ?></div>
                                        </td>
                                        <td>Rp <?= number_format((float)$row['harga_jual'], 0, ',', '.'); ?></td>
                                        <td>
                                            <input type="number" name="qty[<?= (int)$row['id_barang']; ?>]" min="0" max="<?= (int)$row['stok']; ?>" value="<?= (int)$row['qty']; ?>" style="width: 90px; margin-bottom:0;">
                                        </td>
                                        <td>Rp <?= number_format((float)$row['subtotal'], 0, ',', '.'); ?></td>
                                        <td>
                                            <a class="btn btn-danger btn-sm" href="remove_from_cart.php?id=<?= (int)$row['id_barang']; ?>" onclick="return confirm('Hapus item ini dari keranjang?')">
                                                <i class="fas fa-trash-alt"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div style="display:flex; justify-content: space-between; align-items: center; padding: 1rem; gap: 1rem; flex-wrap: wrap;">
                            <div style="font-weight: 700; font-size: 1.1rem;">Total: Rp <?= number_format((float)$total, 0, ',', '.'); ?></div>
                            <div style="display:flex; gap: 0.5rem;">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-sync"></i> Update Keranjang
                                </button>
                                <a href="checkout.php" class="btn btn-success">
                                    <i class="fas fa-credit-card"></i> Checkout
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
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
