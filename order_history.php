<?php
require_once __DIR__ . '/includes/auth.php';
require_user_login();
$user = current_user();
$mysqli = connect_db();
$query = "SELECT o.id, o.total_price, o.status, o.created_at, o.shipping_address,
            p.status AS payment_status, p.payment_method,
            GROUP_CONCAT(CONCAT(oi.quantity, '× ', COALESCE(cs.crop_name, 'Deleted Crop')) SEPARATOR ', ') AS products
          FROM orders o
          LEFT JOIN payments p ON p.order_id = o.id
          LEFT JOIN order_items oi ON oi.order_id = o.id
          LEFT JOIN crop_stock cs ON cs.id = oi.crop_id
          WHERE o.user_id = ?
          GROUP BY o.id
          ORDER BY o.created_at DESC";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Agriculture Portal</title>
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
                <li class="nav-item"><a class="nav-link active" href="order_history.php">My Orders</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container py-4">
    <h1 class="h3 mb-4">My Orders</h1>
    <?php if ($result->num_rows === 0): ?>
        <div class="alert alert-info">No orders found. Start shopping to place your first order.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Products</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Method</th>
                        <th>Ordered</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['products']); ?></td>
                            <td>₹<?php echo number_format($order['total_price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                            <td><?php echo htmlspecialchars($order['payment_status'] ?? 'Pending'); ?></td>
                            <td><?php echo htmlspecialchars($order['payment_method'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                            <td><a href="payment.php?order_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-success">View</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
