CREATE DATABASE IF NOT EXISTS tada_sando CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tada_sando;

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  category VARCHAR(30) NOT NULL,
  description VARCHAR(255) NOT NULL,
  price INT NOT NULL,
  bg_color CHAR(7) NOT NULL,
  fill_color CHAR(7) NOT NULL,
  is_best TINYINT(1) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  image VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE products
  ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS image VARCHAR(255) DEFAULT NULL;

CREATE TABLE IF NOT EXISTS product_ratings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  name VARCHAR(40) NOT NULL,
  rating TINYINT NOT NULL,
  comment VARCHAR(300) DEFAULT '',
  ip_hash CHAR(64) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  INDEX (product_id)
);

CREATE TABLE IF NOT EXISTS site_ratings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(40) NOT NULL,
  rating TINYINT NOT NULL,
  comment VARCHAR(300) DEFAULT '',
  ip_hash CHAR(64) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(40) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_code VARCHAR(20) NOT NULL UNIQUE,
  customer_name VARCHAR(80) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  address VARCHAR(300) NOT NULL,
  note VARCHAR(200) DEFAULT '',
  subtotal INT NOT NULL,
  shipping INT NOT NULL DEFAULT 0,
  total INT NOT NULL,
  payment_method ENUM('transfer','qris','cod') NOT NULL,
  status ENUM('menunggu_pembayaran','menunggu_verifikasi','diproses','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu_pembayaran',
  proof_file VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (status)
);

CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT DEFAULT NULL,
  name VARCHAR(100) NOT NULL,
  price INT UNSIGNED NOT NULL,
  qty TINYINT UNSIGNED NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
  INDEX (order_id)
);

UPDATE products
SET name = 'Sando strawberry', category = 'Fruit',
    description = 'Strawberry segar dengan krim lembut di antara roti empuk.',
    price = 7000, bg_color = '#FFC9D8', fill_color = '#F0527E', is_best = 1, is_active = 1
WHERE name IN ('Strawberry Cloud Sando', 'Sando strawberry');
UPDATE products
SET name = 'Sando mangga', category = 'Fruit',
    description = 'Mangga manis dengan krim lembut di antara roti empuk.',
    price = 7000, bg_color = '#FFE48A', fill_color = '#FFA928', is_best = 1, is_active = 1
WHERE name IN ('Mango Sunshine Sando', 'Sando mangga');

UPDATE order_items oi
JOIN products p ON p.id = oi.product_id
SET oi.product_id = NULL
WHERE p.name NOT IN ('Sando strawberry', 'Sando mangga', 'Sando anggur', 'Sando mix');
DELETE r FROM product_ratings r
JOIN products p ON p.id = r.product_id
WHERE p.name NOT IN ('Sando strawberry', 'Sando mangga', 'Sando anggur', 'Sando mix');
DELETE FROM products
WHERE name NOT IN ('Sando strawberry', 'Sando mangga', 'Sando anggur', 'Sando mix');

INSERT INTO products (name, category, description, price, bg_color, fill_color, is_best)
SELECT 'Sando strawberry','Fruit','Strawberry segar dengan krim lembut di antara roti empuk.',7000,'#FFC9D8','#F0527E',1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Sando strawberry');
INSERT INTO products (name, category, description, price, bg_color, fill_color, is_best)
SELECT 'Sando mangga','Fruit','Mangga manis dengan krim lembut di antara roti empuk.',7000,'#FFE48A','#FFA928',1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Sando mangga');
INSERT INTO products (name, category, description, price, bg_color, fill_color, is_best)
SELECT 'Sando anggur','Fruit','Anggur segar dengan krim lembut di antara roti empuk.',7000,'#E2D4F5','#6B5BC4',0
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Sando anggur');
INSERT INTO products (name, category, description, price, bg_color, fill_color, is_best)
SELECT 'Sando mix','Fruit','Perpaduan strawberry, mangga, dan anggur dengan krim lembut.',10000,'#FFE9A3','#F2658F',0
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Sando mix');

INSERT INTO site_ratings (name, rating, comment, ip_hash)
SELECT 'Nadia',5,'Websitenya cerah dan gampang buat pesan.','seed1' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM site_ratings WHERE ip_hash = 'seed1');
INSERT INTO site_ratings (name, rating, comment, ip_hash)
SELECT 'Rafi',5,'Menunya jelas, rating-nya membantu memilih.','seed2' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM site_ratings WHERE ip_hash = 'seed2');
INSERT INTO site_ratings (name, rating, comment, ip_hash)
SELECT 'Salsa',4,'Tampilannya lucu, loadingnya cepat.','seed3' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM site_ratings WHERE ip_hash = 'seed3');
