<?php
require_once 'config.php';
if (!isAuth()) { header('Location: login.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$items = [];
if ($_SESSION['cart']) {
    $ids = array_keys($_SESSION['cart']);
    $in  = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($in)");
    $stmt->execute($ids);
    $items = $stmt->fetchAll();
}
$total = cartTotal($pdo);

$slots = ['09:00–12:00', '12:00–15:00', '15:00–18:00', '18:00–21:00', 'Как можно быстрее'];
$methods = [
    'sbp'   => ['СБП — по QR-коду', 'Мгновенно, без комиссии', false],
    'card'  => ['Банковская карта', 'Оплата на сайте', true],
    'cash'  => ['Наличные курьеру', 'Оплата при получении', true],
];

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $items) {
    $address = trim($_POST['address'] ?? '');
    $slot    = in_array($_POST['slot'] ?? '', $slots, true) ? $_POST['slot'] : $slots[count($slots)-1];
    $pmKey   = $_POST['pay'] ?? 'sbp';
    if (!isset($methods[$pmKey]) || $methods[$pmKey][2]) {
        $err = 'Выберите СБП — другие способы появятся после запуска (демо).';
    } elseif (mb_strlen($address) < 8) {
        $err = 'Укажите адрес доставки (город, улица, дом, квартира).';
    } else {
        $pdo->beginTransaction();
        $pdo->prepare("INSERT INTO orders(user_id,total,address,delivery_slot,payment_method,status)
                       VALUES(?,?,?,?,'СБП','pending')")
            ->execute([$_SESSION['user_id'], $total, $address, $slot]);
        $orderId = $pdo->lastInsertId();
        $ins = $pdo->prepare("INSERT INTO order_items(order_id,product_id,quantity,price) VALUES(?,?,?,?)");
        foreach ($items as $p) {
            $ins->execute([$orderId, $p['id'], $_SESSION['cart'][$p['id']], $p['price']]);
        }
        // сохранить адрес в профиль, если там пусто
        if (!$user['address']) {
            $pdo->prepare("UPDATE users SET address=? WHERE id=?")->execute([$address, $_SESSION['user_id']]);
        }
        $pdo->commit();
        $_SESSION['cart'] = [];
        header('Location: payment.php?order=' . $orderId); exit;
    }
}

$title = 'Оформление заказа'; include 'includes/header.php';
?>
<div class="page">
    <div class="wrap" style="max-width:760px">
        <div class="page-head fade-up">
            <h1>Оформление заказа</h1>
            <p class="sub">Адрес, интервал доставки и способ оплаты — три минуты.</p>
        </div>

        <?php if (!$items): ?>
            <div class="empty fade-up d1">
                <div class="e-ic"><?= icon('cart', 32) ?></div>
                <h3>Корзина пуста</h3>
                <p>Нечего оформлять — добавьте товары.</p>
                <a href="catalog.php" class="btn btn-primary">В магазины</a>
            </div>
        <?php else: ?>
            <?php if ($err): ?><div class="alert err fade-up"><?= e($err) ?></div><?php endif; ?>
            <div class="alert demo fade-up">Демо-режим: оплата, SMS и уведомления — заглушки, деньги не списываются.</div>

            <form method="post" class="fade-up d1">
                <div class="card-box" style="margin-bottom:22px">
                    <h2>Куда везём</h2>
                    <div class="f">
                        <label>Адрес доставки</label>
                        <input name="address" required
                               value="<?= e($_POST['address'] ?? $user['address'] ?? '') ?>"
                               placeholder="Нефтеюганск, ул. Дружбы Народов, 15, кв. 42">
                        <div class="hint">Сохраним в профиле — в следующий раз подставится само.</div>
                    </div>
                    <div class="f-row">
                        <div class="f">
                            <label>Кому позвонить</label>
                            <input type="tel" name="phone" value="<?= e($user['phone'] ?? '') ?>" placeholder="+7…">
                            <div class="hint">Курьер наберёт за 10 минут до прибытия (заглушка).</div>
                        </div>
                        <div class="f">
                            <label>Интервал доставки</label>
                            <select name="slot">
                                <?php foreach ($slots as $s): ?>
                                    <option <?= ($_POST['slot'] ?? '') === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="f" style="margin-bottom:0">
                        <label>Комментарий курьеру</label>
                        <textarea name="comment" placeholder="Домофон 42В, этаж 5 — необязательно" style="min-height:60px"></textarea>
                    </div>
                </div>

                <div class="card-box" style="margin-bottom:22px">
                    <h2>Способ оплаты</h2>
                    <?php foreach ($methods as $key => $m): ?>
                        <label class="opt">
                            <input type="radio" name="pay" value="<?= $key ?>" <?= $key === 'sbp' ? 'checked' : '' ?> <?= $m[2] ? 'disabled' : '' ?>>
                            <span>
                                <span class="tt"><?= e($m[0]) ?></span>
                                <span class="dd" style="display:block"><?= e($m[1]) ?></span>
                            </span>
                            <?php if ($m[2]): ?><span class="soon">Скоро</span><?php endif; ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="card-box">
                    <h2>Проверьте состав</h2>
                    <?php foreach ($items as $p): ?>
                        <div class="order-item">
                            <span><?= e($p['name']) ?> <span class="q">× <?= $_SESSION['cart'][$p['id']] ?></span></span>
                            <span class="p"><?= number_format($p['price'] * $_SESSION['cart'][$p['id']], 2, '.', '') ?> ₽</span>
                        </div>
                    <?php endforeach; ?>
                    <div class="total" style="display:flex; justify-content:space-between; align-items:baseline; padding-top:16px; margin-top:6px; border-top:1px solid var(--line)">
                        <span style="color:var(--ink-2)">К оплате</span>
                        <b style="font-family:var(--serif); font-size:27px"><?= number_format($total, 2, '.', '') ?> ₽</b>
                    </div>
                    <button class="btn btn-primary btn-block btn-lg" type="submit" style="margin-top:20px"><span>Перейти к оплате</span><?= icon('arrow', 19) ?></button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
