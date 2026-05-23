<?php
require_once __DIR__ . '/includes/auth.php';
require_user_login();
$user = current_user();
$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$mysqli = connect_db();
$payment = null;
$order = null;
$message = '';
$error = '';
$stmt = $mysqli->prepare('SELECT o.*, p.id AS payment_id, p.amount AS payment_amount, p.status AS payment_status, p.payment_method, p.paid_at FROM orders o JOIN payments p ON p.order_id = o.id WHERE o.id = ? AND o.user_id = ? LIMIT 1');
$stmt->bind_param('ii', $orderId, $user['id']);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();
if (!$order) {
    header('Location: order_history.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = $_POST['payment_method'] ?? 'COD';
    $validMethods = ['COD', 'UPI', 'Card'];
    if (!in_array($paymentMethod, $validMethods, true)) {
        $error = 'Select a valid payment method.';
    } else {
        $status = 'Paid';
        $paidAt = date('Y-m-d H:i:s');
        $update = $mysqli->prepare('UPDATE payments SET payment_method = ?, status = ?, paid_at = ? WHERE id = ?');
        $update->bind_param('sssi', $paymentMethod, $status, $paidAt, $order['payment_id']);
        $update->execute();
        $update->close();
        $updateOrder = $mysqli->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $confirmed = 'Confirmed';
        $updateOrder->bind_param('si', $confirmed, $orderId);
        $updateOrder->execute();
        $updateOrder->close();
        $message = 'Payment completed successfully. Your order is confirmed.';
        $order['payment_status'] = 'Paid';
        $order['payment_method'] = $paymentMethod;
        $order['paid_at'] = $paidAt;
        $order['status'] = 'Confirmed';
    }
}
$itemResult = $mysqli->prepare('SELECT oi.quantity, oi.unit_price, cs.crop_name FROM order_items oi LEFT JOIN crop_stock cs ON cs.id = oi.crop_id WHERE oi.order_id = ?');
$itemResult->bind_param('i', $orderId);
$itemResult->execute();
$itemData = $itemResult->get_result();
$items = $itemData->fetch_all(MYSQLI_ASSOC);
$itemResult->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Agriculture Portal</title>
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
                <li class="nav-item"><a class="nav-link active" href="payment.php?order_id=<?php echo $orderId; ?>">Payment</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container py-4">
    <h1 class="h3 mb-4">Payment for Order #<?php echo $orderId; ?></h1>
    <?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">Order Details</h5>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
                    <p><strong>Total:</strong> ₹<?php echo number_format($order['total_price'], 2); ?></p>
                    <p><strong>Shipping Address:</strong><br><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                    <div class="mt-3">
                        <h6>Items</h6>
                        <ul class="list-group">
                            <?php foreach ($items as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($item['crop_name'] ?? 'Deleted crop'); ?> × <?php echo (int)$item['quantity']; ?>
                                    <span>₹<?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php if ($order['payment_status'] !== 'Paid'): ?>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Payment Method</h5>
                        <form method="post" action="payment.php?order_id=<?php echo $orderId; ?>">
                            <div class="mb-3">
                                <label class="form-label">Select Payment Method</label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="COD"<?php echo $order['payment_method'] === 'COD' ? ' selected' : ''; ?>>Cash on Delivery</option>
                                    <option value="UPI"<?php echo $order['payment_method'] === 'UPI' ? ' selected' : ''; ?>>UPI</option>
                                    <option value="Card"<?php echo $order['payment_method'] === 'Card' ? ' selected' : ''; ?>>Card</option>
                                </select>
                            </div>
                            <button class="btn btn-success">Finish Payment</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-success">Payment already completed. Thank you for your order.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
