<?php
require_once 'config.php';

if (isset($_GET['plus'])) {
    $id = (int)$_GET['plus'];
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
    header('Location: cart.php'); exit;
}
if (isset($_GET['minus'])) {
    $id = (int)$_GET['minus'];
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) - 1;
    if ($_SESSION['cart'][$id] <= 0) unset($_SESSION['cart'][$id]);
    header('Location: cart.php'); exit;
}
if (isset($_GET['del'])) {
    unset($_SESSION['cart'][(int)$_GET['del']]);
    header('Location: cart.php'); exit;
}

$items = [];
if ($_SESSION['cart']) {
    $ids = array_keys($_SESSION['cart']);
    $in  = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($in)");
    $stmt->execute($ids);
    $items = $stmt->fetchAll();
}
$total = cartTotal($pdo);
$discount = 0;
foreach ($items as $p) {
    if ($p['old_price']) $discount += ($p['old_price'] - $p['price']) * $_SESSION['cart'][$p['id']];
}

$title = 'Корзина'; include 'includes/header.php';
?>
<div class="page">
    <div class="wrap">
        <div class="page-head fade-up">
            <h1>Корзина</h1>
            <p class="sub"><?= $items ? 'Проверьте позиции и переходите к оформлению' : 'Ваши выбранные товары' ?></p>
        </div>

        <?php if (!$items): ?>
            <div class="empty fade-up d1">
                <div class="e-ic"><?= icon('cart', 32) ?></div>
                <h3>Здесь пока пусто</h3>
                <p>Добавьте товары из «Магнита» — доставка бесплатная.</p>
                <a href="catalog.php" class="btn btn-primary"><span>Перейти в магазины</span><?= icon('arrow', 18) ?></a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <div class="fade-up d1">
                    <?php foreach ($items as $p): $q = $_SESSION['cart'][$p['id']]; ?>
                        <div class="cart-item">
                            <img src="<?= e($p['image']) ?>" alt="" onerror="this.style.opacity=.15">
                            <div class="i">
                                <h3><?= e($p['name']) ?></h3>
                                <p><?= e($p['description']) ?></p>
                            </div>
                            <div class="qty">
                                <a href="?minus=<?= $p['id'] ?>" title="Меньше">−</a>
                                <span><?= $q ?></span>
                                <a href="?plus=<?= $p['id'] ?>" title="Больше">+</a>
                            </div>
                            <div class="line"><?= number_format($p['price'] * $q, 2, '.', '') ?> ₽</div>
                            <a href="?del=<?= $p['id'] ?>" class="del" title="Удалить"><?= icon('close', 15) ?></a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <aside class="summary fade-up d2">
                    <h3>Итого</h3>
                    <div class="l"><span>Товары (<?= array_sum($_SESSION['cart']) ?>)</span><b><?= number_format($total + $discount, 2, '.', '') ?> ₽</b></div>
                    <?php if ($discount > 0): ?>
                        <div class="l"><span>Скидки партнёров</span><b style="color:var(--green)">−<?= number_format($discount, 2, '.', '') ?> ₽</b></div>
                    <?php endif; ?>
                    <div class="l"><span>Доставка</span><span class="free-ship">бесплатно</span></div>
                    <div class="total"><span>К оплате</span><b><?= number_format($total, 2, '.', '') ?> ₽</b></div>

                    <div class="promo">
                        <input placeholder="Промокод" data-stub="Демо-режим: промокоды — заглушка">
                        <button class="btn btn-ghost btn-sm" type="button" data-stub="Демо-режим: промокоды — заглушка">Применить</button>
                    </div>

                    <?php if (isAuth()): ?>
                        <a href="checkout.php" class="btn btn-primary btn-block btn-lg"><span>Оформить заказ</span><?= icon('arrow', 19) ?></a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary btn-block btn-lg"><span>Войти для оформления</span><?= icon('arrow', 19) ?></a>
                    <?php endif; ?>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
