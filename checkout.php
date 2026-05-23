<?php
require_once __DIR__ . '/includes/auth.php';
require_user_login();
$user = current_user();
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: shop.php');
    exit;
}
$mysqli = connect_db();
$errors = [];
$success = '';
$cart = $_SESSION['cart'];
$items = [];
$total = 0.0;
$ids = implode(',', array_map('intval', array_keys($cart)));
$result = $mysqli->query("SELECT * FROM crop_stock WHERE id IN ({$ids})");
while ($row = $result->fetch_assoc()) {
    $qty = $cart[$row['id']];
    if ($qty > $row['quantity']) {
        $errors[] = 'Not enough stock for ' . htmlspecialchars($row['crop_name']) . '.';
    }
    $row['quantity_selected'] = $qty;
    $row['subtotal'] = $qty * $row['unit_price'];
    $total += $row['subtotal'];
    $items[] = $row;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['shipping_address'] ?? '');
    if ($address === '') {
        $errors[] = 'Shipping address is required.';
    }
    if (empty($errors)) {
        $mysqli->begin_transaction();
        try {
            $lockStmt = $mysqli->prepare('SELECT quantity FROM crop_stock WHERE id = ? FOR UPDATE');
            foreach ($items as $item) {
                $lockStmt->bind_param('i', $item['id']);
                $lockStmt->execute();
                $lockResult = $lockStmt->get_result();
                $locked = $lockResult->fetch_assoc();
                if (!$locked || $locked['quantity'] < $item['quantity_selected']) {
                    throw new Exception('Insufficient stock for ' . $item['crop_name'] . '.');
                }
            }
            $lockStmt->close();

            $status = 'Pending';
            $stmt = $mysqli->prepare('INSERT INTO orders (user_id, total_price, status, shipping_address) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('idss', $user['id'], $total, $status, $address);
            $stmt->execute();
            $orderId = $stmt->insert_id;
            $stmt->close();

            $itemStmt = $mysqli->prepare('INSERT INTO order_items (order_id, crop_id, quantity, unit_price) VALUES (?, ?, ?, ?)');
            $stockStmt = $mysqli->prepare('UPDATE crop_stock SET quantity = quantity - ? WHERE id = ?');
            foreach ($items as $item) {
                $itemStmt->bind_param('iiid', $orderId, $item['id'], $item['quantity_selected'], $item['unit_price']);
                $itemStmt->execute();
                $stockStmt->bind_param('ii', $item['quantity_selected'], $item['id']);
                $stockStmt->execute();
            }
            $itemStmt->close();
            $stockStmt->close();

            $paymentMethod = 'COD';
            $paymentStatus = 'Pending';
            $paymentStmt = $mysqli->prepare('INSERT INTO payments (order_id, amount, payment_method, status) VALUES (?, ?, ?, ?)');
            $paymentStmt->bind_param('idss', $orderId, $total, $paymentMethod, $paymentStatus);
            $paymentStmt->execute();
            $paymentStmt->close();
            $mysqli->commit();
            $_SESSION['cart'] = [];
            header('Location: payment.php?order_id=' . $orderId);
            exit;
        } catch (Exception $e) {
            $mysqli->rollback();
            $errors[] = 'Unable to create the order: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Agriculture Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container">
        <a class="navbar-brand" href="index.php">Agriculture Portal</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
                <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
                <li class="nav-item"><a class="nav-link active" href="checkout.php">Checkout</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container py-4">
    <h1 class="h3 mb-4">Confirm Your Order</h1>
    <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?php echo htmlspecialchars($error); ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <div class="row">
        <div class="col-lg-7">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">Order Summary</h5>
                    <?php foreach ($items as $item): ?>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <div>
                                <strong><?php echo htmlspecialchars($item['crop_name']); ?></strong><br>
                                Qty: <?php echo $item['quantity_selected']; ?> × ₹<?php echo number_format($item['unit_price'], 2); ?>
                            </div>
                            <div>₹<?php echo number_format($item['subtotal'], 2); ?></div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex justify-content-between mt-3 fw-bold">
                        <span>Total</span>
                        <span>₹<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Shipping Address</h5>
                    <form method="post" action="checkout.php">
                        <div class="mb-3">
                            <label for="shipping_address" class="form-label">Address</label>
                            <textarea id="shipping_address" name="shipping_address" class="form-control" rows="4" required><?php echo htmlspecialchars($_POST['shipping_address'] ?? ''); ?></textarea>
                        </div>
                        <button class="btn btn-success">Place Order</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
