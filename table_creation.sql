-- database.sql
CREATE DATABASE IF NOT EXISTS batik_artisan;
USE batik_artisan;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    phone VARCHAR(20),
    role ENUM('customer', 'artisan') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    artisan_id INT,
    name VARCHAR(200),
    slug VARCHAR(200) UNIQUE,
    description TEXT,
    price DECIMAL(10,2),
    category ENUM('fabric', 'clothing', 'home_decor', 'accessories'),
    stock INT DEFAULT 0,
    status TINYINT DEFAULT 1,
    artisan_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (artisan_id) REFERENCES users(id)
);

-- Product images
CREATE TABLE product_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT,
    image_path VARCHAR(500),
    sort_order INT DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Product variants
CREATE TABLE product_variants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT,
    size VARCHAR(50),
    price_adjustment DECIMAL(10,2) DEFAULT 0,
    stock INT DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Orders
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    order_number VARCHAR(20) UNIQUE,
    total DECIMAL(10,2),
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT,
    shipping_method VARCHAR(50),
    tracking_number VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order items
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    variant_id INT,
    quantity INT,
    price DECIMAL(10,2),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Bookings (live sessions)
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    session_date DATE,
    session_time VARCHAR(20),
    group_size INT,
    special_requests TEXT,
    deposit_amount DECIMAL(10,2),
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Wishlist
CREATE TABLE wishlist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    product_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    UNIQUE KEY unique_wishlist (user_id, product_id)
);

-- Reviews
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT,
    user_id INT,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert sample artisan
INSERT INTO users (name, email, password, phone, role) VALUES 
('Malini Weerasinghe', 'malini@batiksl.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+94771234567', 'artisan');

-- Insert sample products
INSERT INTO products (artisan_id, name, slug, description, price, category, stock) VALUES
(1, 'Traditional Elephant Batik Sarong', 'elephant-batik-sarong', 'Handcrafted sarong featuring traditional elephant motifs', 4500.00, 'clothing', 15),
(1, 'Blue Floral Batik Cushion Cover', 'floral-batik-cushion', 'Cotton cushion cover with floral batik pattern', 1800.00, 'home_decor', 30),
(1, 'Batik Wall Hanging - Sun & Moon', 'sun-moon-wall-hanging', 'Large wall hanging with sun and moon design', 3500.00, 'home_decor', 8),
(1, 'Handmade Batik Scarf - Silk Blend', 'silk-batik-scarf', 'Luxury silk blend scarf, hand-dyed', 2800.00, 'accessories', 20),
(1, 'Batik Cotton Fabric - 2m', 'batik-cotton-fabric', 'Cotton fabric perfect for dressmaking', 2200.00, 'fabric', 50);


INSERT INTO product_variants (product_id, size, price_adjustment, stock) VALUES
(1, 'S', 0, 5), (1, 'M', 0, 5), (1, 'L', 0, 3), (1, 'XL', 100, 2);