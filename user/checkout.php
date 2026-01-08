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
    $sql = "SELECT id_barang, nama, harga_jual, stok FROM data_barang WHERE id_barang IN ($placeholders)";

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (count($items) === 0) {
        header('Location: shop.php');
        exit;
    }

    $ok = true;

    mysqli_begin_transaction($conn);

    foreach ($items as $row) {
        $id = (int)$row['id_barang'];
        $qty = (int)$row['qty'];

        $stmt = $conn->prepare('UPDATE data_barang SET stok = stok - ? WHERE id_barang = ? AND stok >= ?');
        $stmt->bind_param('iii', $qty, $id, $qty);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($affected <= 0) {
            $ok = false;
            break;
        }
    }

    if ($ok) {
        mysqli_commit($conn);
        $_SESSION['cart'] = [];
        header('Location: shop.php?status=checkout_success');
        exit;
    }

    mysqli_rollback($conn);
    header('Location: shop.php?status=checkout_failed');
    exit;
}

$cartCount = 0;
foreach ($_SESSION['cart'] as $qty) {
    $cartCount += (int)$qty;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
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
                <h1 class="page-title">Checkout</h1>
                <a href="cart.php" class="btn">
                    <i class="fas fa-arrow-left"></i> Kembali ke Keranjang
                </a>
            </div>

            <?php if (count($items) === 0): ?>
                <div class="empty-state">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Keranjang kosong</h3>
                    <p>Tambahkan barang dulu sebelum checkout.</p>
                    <a href="shop.php" class="btn" style="margin-top: 1rem;">
                        <i class="fas fa-store"></i> Ke Katalog
                    </a>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th>Harga</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['nama']); ?></td>
                                    <td>Rp <?= number_format((float)$row['harga_jual'], 0, ',', '.'); ?></td>
                                    <td><?= (int)$row['qty']; ?></td>
                                    <td>Rp <?= number_format((float)$row['subtotal'], 0, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div style="display:flex; justify-content: space-between; align-items: center; padding: 1rem; gap: 1rem; flex-wrap: wrap;">
                        <div style="font-weight: 700; font-size: 1.1rem;">Total: Rp <?= number_format((float)$total, 0, ',', '.'); ?></div>
                        <form method="post" action="checkout.php" style="margin:0;">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check"></i> Konfirmasi Checkout
                            </button>
                        </form>
                    </div>
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
