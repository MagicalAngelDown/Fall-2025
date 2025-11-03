CREATE DATABASE IF NOT EXISTS shopping_cart
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE shopping_cart;

-- Drop & recreate for a clean run during testing (comment out in production)
DROP TABLE IF EXISTS products;

CREATE TABLE products (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  description TEXT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed data
INSERT INTO products (name, price, description) VALUES
('Wireless Mouse', 19.99, 'Ergonomic 2.4GHz mouse with USB receiver.'),
('Mechanical Keyboard', 79.95, 'Hot‑swappable switches and RGB backlight.'),
('USB‑C Cable (2m)', 8.49, 'USB‑C to USB‑C, 60W fast charge support.'),
('27" 1440p Monitor', 259.00, 'IPS panel, 75Hz, thin bezels.'),
('Laptop Stand', 24.99, 'Aluminum, adjustable height and angle.');