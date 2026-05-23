<?php
require_once __DIR__ . '/includes/auth.php';
$mysqli = connect_db();
$messages = [];
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (isset($_GET['action']) && $_GET['action'] === 'add' && !empty($_GET['id'])) {
    $cropId = (int)$_GET['id'];
    $quantity = max(1, (int)($_GET['qty'] ?? 1));
    $stmt = $mysqli->prepare('SELECT quantity FROM crop_stock WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $cropId);
    $stmt->execute();
    $result = $stmt->get_result();
    $crop = $result->fetch_assoc();
    $stmt->close();
    if (!$crop) {
        $messages[] = ['type' => 'danger', 'text' => 'Crop not found.'];
    } elseif ($crop['quantity'] < $quantity) {
        $messages[] = ['type' => 'danger', 'text' => 'Requested quantity exceeds available stock.'];
    } else {
        $_SESSION['cart'][$cropId] = ($_SESSION['cart'][$cropId] ?? 0) + $quantity;
        $messages[] = ['type' => 'success', 'text' => 'Crop added to cart.'];
    }
}
$result = $mysqli->query('SELECT * FROM crop_stock ORDER BY created_at DESC');
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Crops - Agriculture Portal</title>
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
                <li class="nav-item"><a class="nav-link active" href="shop.php">Shop</a></li>
                <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
                <?php if ($user): ?>
                    <li class="nav-item"><a class="nav-link" href="order_history.php">My Orders</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Available Crops</h1>
        <a class="btn btn-outline-light btn-success" href="cart.php">View Cart</a>
    </div>
    <?php foreach ($messages as $message): ?>
        <div class="alert alert-<?php echo htmlspecialchars($message['type']); ?>"><?php echo htmlspecialchars($message['text']); ?></div>
    <?php endforeach; ?>
    <div class="row g-4">
        <?php while ($crop = $result->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <?php if ($crop['image_path']): ?>
                        <a href="<?php echo htmlspecialchars($crop['image_path']); ?>" target="_blank" rel="noopener noreferrer" title="View <?php echo htmlspecialchars($crop['crop_name']); ?> image">
                            <img src="<?php echo htmlspecialchars($crop['image_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($crop['crop_name']); ?>" style="object-fit:cover; height:200px;">
                        </a>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($crop['crop_name']); ?></h5>
                        <p class="card-text mb-1"><?php echo htmlspecialchars($crop['description']); ?></p>
                        <p class="mb-1"><strong>Price:</strong> ₹<?php echo number_format($crop['unit_price'], 2); ?></p>
                        <p class="mb-3"><strong>Stock:</strong> <?php echo (int)$crop['quantity']; ?></p>
                        <div class="mt-auto">
                            <form method="get" action="shop.php" class="d-flex gap-2">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="id" value="<?php echo $crop['id']; ?>">
                                <input type="number" name="qty" value="1" min="1" max="<?php echo max(1, (int)$crop['quantity']); ?>" class="form-control form-control-sm" style="width:90px;">
                                <button class="btn btn-success btn-sm" <?php echo $crop['quantity'] <= 0 ? 'disabled' : ''; ?>>Add</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
