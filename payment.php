<?php
require_once 'config.php';
if (!isAuth()) { header('Location: login.php'); exit; }

$orderId = (int)($_GET['order'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id=? AND user_id=?");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();
if (!$order) { die('Заказ не найден'); }

// позиции заказа
$items = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?");
$items->execute([$orderId]);
$items = $items->fetchAll();

$title = 'Оплата'; include 'includes/header.php';
?>
<div class="page">
    <div class="wrap">
        <div class="pay-shell fade-up">
            <h1 style="font-size:32px; margin-bottom:8px">Оплата по СБП</h1>
            <p style="color:var(--ink-2); font-size:15px">
                Заказ №<?= $order['id'] ?> · <b><?= number_format($order['total'], 2, '.', '') ?> ₽</b>
            </p>

            <div class="alert demo">
                Демо-режим: <b>деньги не списываются</b>. QR-код сгенерирован из заглушки.
            </div>

            <div class="qr-frame">
                <img src="qr.php?order=<?= $order['id'] ?>" alt="QR-код для оплаты" width="260" height="260">
            </div>

            <h3 style="font-size:19px; margin-bottom:10px">Как оплатить</h3>
            <ol class="pay-steps">
                <li>Откройте приложение вашего банка</li>
                <li>Выберите «Оплата по QR» или «Оплата через СБП»</li>
                <li>Наведите камеру на QR-код выше</li>
                <li>Подтвердите сумму — деньги <i>не спишутся</i> в демо-режиме</li>
            </ol>

            <a href="profile.php?paid=<?= $order['id'] ?>" class="btn btn-primary btn-lg">
                <?= icon('check', 19) ?><span>Я оплатил — перейти к заказу</span>
            </a>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
