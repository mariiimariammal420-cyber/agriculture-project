<?php
require_once __DIR__ . '/includes/auth.php';
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['remove_id'])) {
        $removeId = (int)$_POST['remove_id'];
        unset($_SESSION['cart'][$removeId]);
        $message = 'Item removed from cart.';
    }
    if (!empty($_POST['update_quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $cropId => $qty) {
            $cropId = (int)$cropId;
            $qty = max(0, (int)$qty);
            if ($qty === 0) {
                unset($_SESSION['cart'][$cropId]);
            } else {
                $_SESSION['cart'][$cropId] = $qty;
            }
        }
        $message = 'Cart updated successfully.';
    }
}
$cart = $_SESSION['cart'];
$items = [];
$total = 0.0;
$mysqli = connect_db();
if (!empty($cart)) {
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $result = $mysqli->query("SELECT * FROM crop_stock WHERE id IN ({$ids})");
    while ($row = $result->fetch_assoc()) {
        $row['quantity_selected'] = $cart[$row['id']];
        $row['subtotal'] = $row['quantity_selected'] * $row['unit_price'];
        $total += $row['subtotal'];
        $items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Agriculture Portal</title>
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
                <li class="nav-item"><a class="nav-link active" href="cart.php">Cart</a></li>
                <li class="nav-item"><a class="nav-link" href="checkout.php">Checkout</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container py-4">
    <h1 class="h3 mb-4">Your Cart</h1>
    <?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if (empty($items)): ?>
        <div class="alert alert-info">Your cart is empty. <a href="shop.php">Browse crops</a> to add items.</div>
    <?php else: ?>
        <form method="post" action="cart.php">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Crop</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['crop_name']); ?></td>
                                <td>₹<?php echo number_format($item['unit_price'], 2); ?></td>
                                <td><input type="number" name="quantities[<?php echo $item['id']; ?>]" value="<?php echo $item['quantity_selected']; ?>" min="0" max="<?php echo $item['quantity']; ?>" class="form-control form-control-sm" style="width:100px;"></td>
                                <td>₹<?php echo number_format($item['subtotal'], 2); ?></td>
                                <td>
                                    <button name="remove_id" value="<?php echo $item['id']; ?>" class="btn btn-sm btn-danger">Remove</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div><strong>Total:</strong> ₹<?php echo number_format($total, 2); ?></div>
                <div>
                    <button type="submit" name="update_quantities" value="1" class="btn btn-secondary">Update Cart</button>
                    <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
