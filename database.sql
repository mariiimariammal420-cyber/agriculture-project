CREATE DATABASE IF NOT EXISTS `agriculture` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `agriculture`;

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    expense_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS crop_stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    crop_name VARCHAR(150) NOT NULL,
    description TEXT,
    quantity INT NOT NULL DEFAULT 0,
    unit_price DECIMAL(12,2) NOT NULL,
    image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS income_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source VARCHAR(200) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    income_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_price DECIMAL(12,2) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    shipping_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    crop_id INT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (crop_id) REFERENCES crop_stock(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_method VARCHAR(100) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO admins (name, email, password_hash) VALUES
('Administrator', 'admin@example.com', '$2y$10$qPAbwXNaQHu7RB8alVFB5e4hY7wnUrc45f9/t2fgMiKovTGNCjQFu')
ON DUPLICATE KEY UPDATE email = email;

INSERT INTO users (name, email, password_hash) VALUES
('Customer User', 'user@example.com', '$2y$10$4HCA7GCzhqR4K1omGAkq.uL/obvGVrs0GNxbglUKggW6VJ9/R7vNK')
ON DUPLICATE KEY UPDATE email = email;

INSERT INTO crop_stock (crop_name, description, quantity, unit_price, image_path) VALUES
('Tomatoes', 'Fresh red tomatoes', 120, 30.00, 'uploads/crops/tomatoes.jpg'),
('Potatoes', 'Organic potatoes', 80, 20.00, 'uploads/crops/potatoes.jpg'),
('Carrots', 'Crunchy carrots', 65, 25.00, 'uploads/crops/carrots.jpg'),
('Brinjal', 'Green brinjal', 50, 15.00, 'uploads/crops/lettuce.jpg'),
('Cucumbers', 'Fresh cucumbers', 70, 18.00, 'uploads/crops/cucumbers.jpg'),
('bananas', 'Ripe bananas', 100, 10.00, 'uploads/crops/bananas.jpg')
ON DUPLICATE KEY UPDATE crop_name = crop_name;
