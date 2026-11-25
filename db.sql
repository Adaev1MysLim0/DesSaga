CREATE DATABASE IF NOT EXISTS dessega CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dessega;

CREATE TABLE partners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) ENGINE=InnoDB;
INSERT INTO partners (name) VALUES ('Магнит');

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) ENGINE=InnoDB;
INSERT INTO categories (name) VALUES
('Молочные продукты'),('Хлеб'),('Мясо'),('Овощи и фрукты'),
('Напитки'),('Кондитерские изделия'),('Заморозка'),('Бакалея'),('Снеки');

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NULL UNIQUE,
    phone VARCHAR(20) NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    status VARCHAR(255) DEFAULT NULL,
    role VARCHAR(20) DEFAULT 'user',
    address VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    partner_id INT NOT NULL,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    old_price DECIMAL(10,2) DEFAULT NULL,
    image VARCHAR(255) NOT NULL,
    FOREIGN KEY (partner_id) REFERENCES partners(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB;

INSERT INTO products (partner_id, category_id, name, description, price, old_price, image) VALUES
(1, 1, 'Молочное царство 3,2%', 'Пастеризованное молоко, 1 л', 89.90, 109.00, 'images/milk.jpg'),
(1, 2, 'Хлеб Бородинский', 'Нарезной, 400 г', 45.00, NULL, 'images/bread.jpg'),
(1, 1, 'Сыр Российский', 'Полутвёрдый, 200 г', 189.50, 219.00, 'images/cheese.jpg'),
(1, 6, 'Шоколад Alpen Gold', 'Молочный с орехами, 90 г', 89.00, 119.00, 'images/alpen.jpg'),
(1, 6, 'Шоколад Milka', 'Молочный, 90 г', 99.00, NULL, 'images/milka.jpg'),
(1, 5, 'Сок Добрый апельсин', '1 л', 119.00, 139.00, 'images/juice.jpg'),
(1, 1, 'Кефир Домик в деревне', '1%, 500 мл', 69.00, NULL, 'images/kefir.jpg'),
(1, 9, 'Чипсы Lay''s', 'Сметана и лук, 80 г', 89.00, NULL, 'images/chips.jpg'),
(1, 3, 'Куриное филе', 'Охлаждённое, 1 кг', 349.00, 429.00, 'images/chicken.jpg'),
(1, 4, 'Помидоры черри', '250 г', 159.00, NULL, 'images/tomato.jpg'),
(1, 5, 'Кофе Jacobs Monarch', 'Растворимый, 150 г', 299.00, 349.00, 'images/coffee.jpg'),
(1, 6, 'Шоколад Lindt Excellence', 'Тёмный 70%, 100 г', 249.00, NULL, 'images/lindt.jpg'),
(1, 6, 'Шоколад Kinder Chocolate', 'Молочный, 100 г', 129.00, NULL, 'images/kinder.jpg'),
(1, 2, 'Круассан с миндалём', '180 г', 79.00, NULL, 'images/croissant.jpg'),
(1, 8, 'Макароны Barilla', 'Спагетти, 450 г', 189.00, NULL, 'images/pasta.jpg'),
(1, 5, 'Coca-Cola', 'Классическая, 1.5 л', 129.00, 149.00, 'images/cola.jpg'),
(1, 1, 'Кефир 1%', '500 мл', 65.00, NULL, 'images/kefirkoko.jpg'),
(1, 4, 'Бананы', '1 кг', 89.00, 109.00, 'images/banana.jpg'),
(1, 7, 'Пельмени Сибирские', '800 г', 329.00, 399.00, 'images/pelmeni.jpg'),
(1, 6, 'Шоколад Бабаевский', 'Тёмный, 100 г', 129.00, NULL, 'images/babaevsky.jpg'),
(1, 6, 'Шоколад Сладкий орешек', 'Молочный, 90 г', 59.00, NULL, 'images/oreshek.jpg'),
(1, 5, 'Квас Русский дар', '1.5 л', 79.00, NULL, 'images/kvas.jpg'),
(1, 9, 'Сухарики Хрусteam', 'Сыр, 60 г', 39.00, NULL, 'images/suhariki.jpg'),
(1, 4, 'Авокадо Hass', '2 шт', 199.00, NULL, 'images/avocado.jpg'),
(1, 3, 'Стейк Рибай', 'Охлаждённый, 300 г', 899.00, 1099.00, 'images/steak.jpg'),
(1, 6, 'Шоколад Toblerone', 'Молочный с нуга', 299.00, NULL, 'images/toblerone.jpg'),
(1, 1, 'Йогурт греческий', 'Натуральный, 300 г', 119.00, NULL, 'images/yogurt.jpg'),
(1, 2, 'Хлеб цельнозерновой', '400 г', 99.00, NULL, 'images/wholegrain.jpg'),
(1, 6, 'Шоколад без сахара', 'Стевия, 80 г', 199.00, NULL, 'images/sugarfree.jpg'),
(1, 3, 'Мраморная говядина', 'Охлаждённая, 500 г', 1890.00, NULL, 'images/beef.jpg'),
(1, 6, 'Шоколад Amedei', 'Toscano, 50 г', 890.00, NULL, 'images/amedei.jpg');

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

CREATE TABLE support_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NULL,
    message TEXT NOT NULL,
    reply TEXT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE courier_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    passport_photo VARCHAR(255) NULL,
    self_employed TINYINT(1) DEFAULT 0,
    status VARCHAR(20) DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Аккаунт создателя создаётся через migrate.php (пароль: XzAdaev#2024)
-- INSERT INTO users(name, email, phone, password, role, status) VALUES
-- ('Адаев Муслим', 'xz.adaev@dessega.ru', NULL, 'hashed_password', 'admin', 'Создатель DesSega& · Xz Adaev');