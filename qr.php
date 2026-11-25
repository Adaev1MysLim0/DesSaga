<?php
require_once 'config.php';
$orderId = (int)($_GET['order'] ?? 0);
$stmt = $pdo->prepare("SELECT total FROM orders WHERE id=?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();
if (!$order) { http_response_code(404); exit; }

// Упрощённая СБП-ссылка. В реальной интеграции — данные банка/merchant_id
$sbp = "https://qr.nspk.ru/AS1000".$orderId."?amount=".$order['total']."&currency=RUB";

$data = urlencode($sbp);
$ctx = stream_context_create(['http' => ['timeout' => 6]]);

// Попытка 1 — qrserver.com
$img = @file_get_contents(
    "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=".$data,
    false, $ctx
);

// Попытка 2 — googleapis
if (!$img) {
    $img = @file_get_contents(
        "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=".$data,
        false, $ctx
    );
}

if (!$img) {
    // Fallback: SVG-заглушка с текстом
    header('Content-Type: image/svg+xml');
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300">
        <rect width="300" height="300" fill="#fff"/>
        <text x="150" y="140" text-anchor="middle" font-family="sans-serif" font-size="16" fill="#000">QR временно недоступен</text>
        <text x="150" y="170" text-anchor="middle" font-family="sans-serif" font-size="12" fill="#666">Оплатите заказ №'.$orderId.'</text>
        <text x="150" y="190" text-anchor="middle" font-family="sans-serif" font-size="12" fill="#666">Сумма: '.$order['total'].' ₽</text>
    </svg>';
    exit;
}

header('Content-Type: image/png');
echo $img;