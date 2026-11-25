<?php
if (session_status() === PHP_SESSION_NONE) session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'dessega');
define('DB_USER', 'root');
define('DB_PASS', '');

// --- AI-поддержка (DeepSeek, бесплатно через платформу api.deepseek.com) ---
// Ключ можно получить на https://platform.deepseek.com/api_keys и вписать сюда
// либо задать в переменной окружения DEEPSEEK_API_KEY.
define('DEEPSEEK_API_KEY', getenv('DEEPSEEK_API_KEY') ?: '');
define('DEEPSEEK_API_URL', 'https://api.deepseek.com/chat/completions');
define('DEEPSEEK_MODEL', 'deepseek-chat');

try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    die('<div style="font-family:sans-serif;padding:60px;color:#fff;background:#0a0e1a;min-height:100vh">
        <h1 style="color:#ff4757">Ошибка подключения к БД</h1>
        <p style="color:#8892b0">'.$e->getMessage().'</p>
        <p style="color:#8892b0">Проверьте, что база <b>dessega</b> создана и MySQL запущен.</p></div>');
}

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

function isAuth(): bool { return isset($_SESSION['user_id']); }
function cartCount(): int { return array_sum($_SESSION['cart'] ?? []); }
function cartTotal(PDO $pdo): float {
    if (empty($_SESSION['cart'])) return 0;
    $ids = array_keys($_SESSION['cart']);
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id, price FROM products WHERE id IN ($in)");
    $stmt->execute($ids);
    $total = 0;
    foreach ($stmt as $p) $total += $p['price'] * $_SESSION['cart'][$p['id']];
    return $total;
}
function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Приводит телефон к виду +7XXXXXXXXXX; null — если не телефон
function normalizePhone(string $raw): ?string {
    $d = preg_replace('/\D/', '', $raw);
    if ($d === '' || $d === null) return null;
    if ($d[0] === '8') $d = '7' . substr($d, 1);
    if ($d[0] !== '7') $d = '7' . $d;
    if (strlen($d) !== 11) return null;
    return '+7' . substr($d, 1);
}

function orderStatusLabel(string $s): string {
    return ['pending' => 'Ожидает оплаты', 'paid' => 'Оплачен · собираем', 'done' => 'Доставлен'][$s] ?? $s;
}
function orderStatusClass(string $s): string {
    return ['pending' => 'st-pending', 'paid' => 'st-paid', 'done' => 'st-done'][$s] ?? 'st-pending';
}