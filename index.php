<?php
require_once __DIR__ . '/includes/auth.php';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agriculture Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{background:#f4f7f9;} .hero{padding:80px 0;} .hero h1{font-weight:700;}</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container">
        <a class="navbar-brand" href="index.php">Agriculture Portal</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
                <?php if ($user): ?>
                    <li class="nav-item"><a class="nav-link" href="order_history.php">My Orders</a></li>
                    <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="container hero text-center">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="display-5">Welcome to Agriculture Portal</h1>
            <p class="lead">Buy fresh crops, manage your cart, and checkout using a secure PHP/MySQL application.</p>
            <div class="d-grid d-sm-flex justify-content-sm-center gap-2">
                <a class="btn btn-success btn-lg" href="shop.php">Browse Crops</a>
                <?php if ($user): ?>
                    <a class="btn btn-outline-success btn-lg" href="order_history.php">Order History</a>
                <?php else: ?>
                    <a class="btn btn-outline-success btn-lg" href="register.php">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<div class="container py-4">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Secure Login</h5>
                    <p class="card-text">User registration and login are protected with password hashing.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Crop Catalog</h5>
                    <p class="card-text">View crop stock with images, price, and availability over a responsive interface.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Cart & Checkout</h5>
                    <p class="card-text">Add items to a session cart, confirm orders, and simulate payment in a complete checkout flow.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
