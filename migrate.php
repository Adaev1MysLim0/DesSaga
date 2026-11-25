<?php
/**
 * Миграция схемы БД. Безопасна для повторного запуска:
 * каждый шаг в try/catch, уже применённые шаги пропускаются.
 * Запуск: php migrate.php  (или открыть migrate.php в браузере)
 */
require_once __DIR__ . '/config.php';

$steps = [
    'users.email → NULL'            => "ALTER TABLE users MODIFY email VARCHAR(150) NULL",
    'users.phone → VARCHAR(20)'     => "ALTER TABLE users MODIFY phone VARCHAR(20) NULL",
    'users.phone UNIQUE'            => "ALTER TABLE users ADD UNIQUE INDEX uq_phone (phone)",
    'users.avatar'                  => "ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL AFTER password",
    'users.status'                  => "ALTER TABLE users ADD COLUMN status VARCHAR(255) DEFAULT NULL AFTER avatar",
    'users.role'                    => "ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'user' AFTER status",
    'orders.address'                => "ALTER TABLE orders ADD COLUMN address VARCHAR(255) DEFAULT NULL AFTER total",
    'orders.delivery_slot'          => "ALTER TABLE orders ADD COLUMN delivery_slot VARCHAR(64) DEFAULT NULL AFTER address",
    'orders.payment_method'         => "ALTER TABLE orders ADD COLUMN payment_method VARCHAR(32) DEFAULT NULL AFTER delivery_slot",
    'support_messages'              => "CREATE TABLE IF NOT EXISTS support_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NULL,
        message TEXT NOT NULL,
        reply TEXT NULL,
        status VARCHAR(20) DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    ) ENGINE=InnoDB",
    'courier_applications'          => "CREATE TABLE IF NOT EXISTS courier_applications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        passport_photo VARCHAR(255) NULL,
        self_employed TINYINT(1) DEFAULT 0,
        status VARCHAR(20) DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",
];

$ok = 0; $skip = 0;
foreach ($steps as $label => $sql) {
    try {
        $pdo->exec($sql);
        echo "[OK]   $label\n";
        $ok++;
    } catch (PDOException $e) {
        // 1060 = дубликат колонки, 1061 = дубликат индекса, 1091 = нет ключа
        $code = $e->errorInfo[1] ?? 0;
        if (in_array((int)$code, [1060, 1061, 1091], true)) {
            echo "[SKIP] $label (уже применено)\n";
            $skip++;
        } else {
            echo "[FAIL] $label — {$e->getMessage()}\n";
        }
    }
}

echo "\nГотово: применено $ok, пропущено $skip.\n";

// --- Аккаунт создателя (Xz Adaev): письма из поддержки приходят сюда ---
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute(['xz.adaev@dessega.ru']);
if (!$stmt->fetch()) {
    $hash = password_hash('XzAdaev#2024', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users(name, email, phone, password, role, status) VALUES(?,?,?,?,?,?)")
        ->execute(['Адаев Муслим', 'xz.adaev@dessega.ru', null, $hash, 'admin', 'Создатель DesSega& · Xz Adaev']);
    echo "[OK]   Создан аккаунт создателя: xz.adaev@dessega.ru / XzAdaev#2024\n";
} else {
    $pdo->prepare("UPDATE users SET role='admin' WHERE email=?")->execute(['xz.adaev@dessega.ru']);
    echo "[SKIP] Аккаунт создателя уже существует\n";
}
