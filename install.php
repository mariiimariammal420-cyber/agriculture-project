<?php
require_once __DIR__ . '/includes/db.php';

$mysqli = connect_db(false);
if (!$mysqli->select_db(DB_NAME)) {
    $createDbSql = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if (!$mysqli->query($createDbSql)) {
        die('Unable to create database: ' . $mysqli->error);
    }
    $mysqli->select_db(DB_NAME);
}

$queries = [
    "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS expenses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        description VARCHAR(255) NOT NULL,
        amount DECIMAL(12,2) NOT NULL,
        expense_date DATE NOT NULL,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS crop_stock (
        id INT AUTO_INCREMENT PRIMARY KEY,
        crop_name VARCHAR(150) NOT NULL,
        description TEXT,
        quantity INT NOT NULL DEFAULT 0,
        unit_price DECIMAL(12,2) NOT NULL,
        image_path VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS income_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        source VARCHAR(200) NOT NULL,
        amount DECIMAL(12,2) NOT NULL,
        income_date DATE NOT NULL,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        total_price DECIMAL(12,2) NOT NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'Pending',
        shipping_address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        crop_id INT NULL,
        quantity INT NOT NULL DEFAULT 1,
        unit_price DECIMAL(12,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (crop_id) REFERENCES crop_stock(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        amount DECIMAL(12,2) NOT NULL,
        payment_method VARCHAR(100) NOT NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'Pending',
        paid_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

foreach ($queries as $query) {
    if (!$mysqli->query($query)) {
        die('Error creating table: ' . $mysqli->error);
    }
}

$defaultAdmin = [
    'name' => 'admin',
    'email' => 'admin@example.com',
    'password' => password_hash('admin 123', PASSWORD_DEFAULT),
];

$stmt = $mysqli->prepare('SELECT id FROM admins WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $defaultAdmin['email']);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    $stmt->close();
    $insert = $mysqli->prepare('INSERT INTO admins (name, email, password_hash) VALUES (?, ?, ?)');
    $insert->bind_param('sss', $defaultAdmin['name'], $defaultAdmin['email'], $defaultAdmin['password']);
    $insert->execute();
    $insert->close();
} else {
    $stmt->close();
    $update = $mysqli->prepare('UPDATE admins SET name = ?, password_hash = ? WHERE email = ?');
    $update->bind_param('sss', $defaultAdmin['name'], $defaultAdmin['password'], $defaultAdmin['email']);
    $update->execute();
    $update->close();
}

$sampleUser = [
    'name' => 'Customer User',
    'email' => 'user@example.com',
    'password' => password_hash('User@123', PASSWORD_DEFAULT),
];
$stmt = $mysqli->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $sampleUser['email']);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    $stmt->close();
    $insert = $mysqli->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
    $insert->bind_param('sss', $sampleUser['name'], $sampleUser['email'], $sampleUser['password']);
    $insert->execute();
    $insert->close();
} else {
    $stmt->close();
}

$defaultCrops = [
    ['Tomatoes', 'Fresh red tomatoes', 120, 30.00, 'uploads/crops/tomatoes.jpg'],
    ['Potatoes', 'Organic potatoes', 80, 20.00, 'uploads/crops/potatoes.jpg'],
    ['Carrots', 'Crunchy carrots', 65, 25.00, 'uploads/crops/carrots.jpg'],
];
foreach ($defaultCrops as $crop) {
    $stmt = $mysqli->prepare('SELECT id FROM crop_stock WHERE crop_name = ? LIMIT 1');
    $stmt->bind_param('s', $crop[0]);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        $stmt->close();
        $insert = $mysqli->prepare('INSERT INTO crop_stock (crop_name, description, quantity, unit_price, image_path) VALUES (?, ?, ?, ?, ?)');
        $insert->bind_param('ssids', $crop[0], $crop[1], $crop[2], $crop[3], $crop[4]);
        $insert->execute();
        $insert->close();
    } else {
        $stmt->close();
    }
}

$mysqli->close();

echo "Installation complete. Default admin: admin@example.com / Admin@123\n";
