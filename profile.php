<?php
require_once 'config.php';
if (!isAuth()) { header('Location: login.php'); exit; }
$uid = $_SESSION['user_id'];

// --- действия (все изменения живут на вкладке «Редактирование») ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['act'] ?? '';

    if ($act === 'profile') {
        $name    = trim($_POST['name'] ?? '');
        $status  = trim($_POST['status'] ?? '');
        $phone   = normalizePhone($_POST['phone'] ?? '') ?: null;
        $address = trim($_POST['address'] ?? '');
        if (mb_strlen($name) < 2) {
            $err = 'Имя — минимум 2 символа.';
        } else {
            // телефон не должен конфликтовать с чужим аккаунтом
            if ($phone) {
                $chk = $pdo->prepare("SELECT id FROM users WHERE phone=? AND id<>? LIMIT 1");
                $chk->execute([$phone, $uid]);
                if ($chk->fetch()) $err = 'Этот телефон уже используется в другом аккаунте.';
            }
            if (empty($err)) {
                $pdo->prepare("UPDATE users SET name=?, status=?, phone=?, address=? WHERE id=?")
                    ->execute([$name, $status, $phone, $address, $uid]);
                $_SESSION['user_name'] = $name;
                $msg = 'Профиль обновлён.';
            }
        }
    }

    if ($act === 'avatar' && !empty($_FILES['avatar']['tmp_name'])) {
        $f = $_FILES['avatar'];
        $err = 'Загрузка не удалась.';
        if ($f['error'] === UPLOAD_ERR_OK && $f['size'] <= 2 * 1024 * 1024
            && ($info = @getimagesize($f['tmp_name']))) {
            $allow = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            if (isset($allow[$info['mime']])) {
                $dir = __DIR__ . '/uploads/avatars';
                if (!is_dir($dir)) mkdir($dir, 0777, true);
                $fname = 'u' . $uid . '_' . time() . '.' . $allow[$info['mime']];
                if (move_uploaded_file($f['tmp_name'], $dir . '/' . $fname)) {
                    // удалить старый файл пользователя
                    $old = $pdo->prepare("SELECT avatar FROM users WHERE id=?");
                    $old->execute([$uid]);
                    $oldPath = $old->fetchColumn();
                    if ($oldPath && is_file(__DIR__ . '/' . $oldPath)) @unlink(__DIR__ . '/' . $oldPath);
                    $rel = 'uploads/avatars/' . $fname;
                    $pdo->prepare("UPDATE users SET avatar=? WHERE id=?")->execute([$rel, $uid]);
                    $_SESSION['user_avatar'] = $rel;
                    $msg = 'Фотография обновлена.';
                    $err = '';
                }
            } else {
                $err = 'Можно загружать JPG, PNG или WebP.';
            }
        } elseif ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['size'] > 2 * 1024 * 1024) {
            $err = 'Файл больше 2 МБ — возьмите поменьше.';
        }
    }

    if ($act === 'password') {
        $cur = $_POST['current'] ?? '';
        $new = $_POST['new'] ?? '';
        $rep = $_POST['new2'] ?? '';
        $st = $pdo->prepare("SELECT password FROM users WHERE id=?");
        $st->execute([$uid]);
        $hash = $st->fetchColumn();
        $errs = [];
        if (!password_verify($cur, $hash)) $errs[] = 'текущий пароль неверен';
        if (strlen($new) < 6)             $errs[] = 'новый пароль минимум 6 символов';
        if (!preg_match('/[A-Z]/', $new)) $errs[] = 'нужна заглавная буква';
        if (!preg_match('/[a-z]/', $new)) $errs[] = 'нужна строчная буква';
        if (!preg_match('/[0-9]/', $new)) $errs[] = 'нужна цифра';
        if (!preg_match('/[^A-Za-z0-9]/', $new)) $errs[] = 'нужен спецсимвол';
        if ($new !== $rep)                $errs[] = 'пароли не совпадают';
        if ($errs) {
            $err = 'Пароль не изменён: ' . implode(', ', $errs) . '.';
        } else {
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")
                ->execute([password_hash($new, PASSWORD_DEFAULT), $uid]);
            $msg = 'Пароль изменён.';
        }
    }
}

// --- статусы заказов (заглушка оплаты → жизненный цикл) ---
if (isset($_GET['paid'])) {
    $pdo->prepare("UPDATE orders SET status='paid' WHERE id=? AND user_id=? AND status='pending'")
        ->execute([(int)$_GET['paid'], $uid]);
    header('Location: profile.php?tab=orders'); exit;
}
if (isset($_GET['done'])) {
    $pdo->prepare("UPDATE orders SET status='done' WHERE id=? AND user_id=? AND status='paid'")
        ->execute([(int)$_GET['done'], $uid]);
    header('Location: profile.php?tab=orders'); exit;
}

// --- данные ---
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$uid]);
$user = $stmt->fetch();

$tab = $_GET['tab'] ?? 'profile';
if (!in_array($tab, ['profile', 'orders', 'edit'], true)) $tab = 'profile';
// после сохранения формы остаёмся на вкладке редактирования — там видны ответы
if ($_SERVER['REQUEST_METHOD'] === 'POST') $tab = 'edit';

$orders = $pdo->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY id DESC");
$orders->execute([$uid]);
$orders = $orders->fetchAll();

// позиции всех заказов одним запросом (N+1 не размножаем)
$itemsByOrder = [];
if ($orders) {
    $ids = array_column($orders, 'id');
    $in = implode(',', array_fill(0, count($ids), '?'));
    $it = $pdo->prepare("SELECT oi.order_id, oi.quantity, oi.price, p.name
                         FROM order_items oi JOIN products p ON p.id=oi.product_id
                         WHERE oi.order_id IN ($in) ORDER BY oi.id");
    $it->execute($ids);
    foreach ($it as $row) $itemsByOrder[$row['order_id']][] = $row;
}

$ordersCount  = count($orders);
$unitsCount   = 0;
$spentTotal   = 0.0;
foreach ($orders as $o) {
    $spentTotal += (float)$o['total'];
    foreach ($itemsByOrder[$o['id']] ?? [] as $it) $unitsCount += (int)$it['quantity'];
}
$sinceDays = max(0, (int)floor((time() - strtotime($user['created_at'])) / 86400));
$initial   = mb_strtoupper(mb_substr($user['name'], 0, 1));

$title = 'Личный кабинет'; include 'includes/header.php';
?>
<div class="page">
    <div class="wrap" style="max-width:920px">

        <!-- Шапка профиля в духе ВК: обложка, аватар поверх, имя, статус, счётчики -->
        <div class="profile-shell fade-up">
            <div class="cover"></div>

            <div class="profile-id">
                <div class="avatar-holder">
                    <div class="avatar-big">
                        <?php if ($user['avatar']): ?>
                            <img src="<?= e($user['avatar']) ?>" alt="<?= e($user['name']) ?>">
                        <?php else: ?>
                            <?= e($initial) ?>
                        <?php endif; ?>
                    </div>
                    <span class="online" title="Вы в сети"></span>
                </div>

                <div class="who">
                    <h1><?= e($user['name']) ?> <span class="vid">клиент</span></h1>
                    <div class="profile-status<?= $user['status'] ? '' : ' is-empty' ?>">
                        <?= $user['status'] ? e($user['status']) : 'Статус не указан' ?>
                    </div>
                    <div class="profile-subline">
                        <span class="live">вы в сети</span>
                        <span class="sep"></span>
                        <span>ID #<?= (int)$user['id'] ?></span>
                        <span class="sep"></span>
                        <span>с нами с <?= date('d.m.Y', strtotime($user['created_at'])) ?></span>
                    </div>
                </div>

                <div class="profile-actions">
                    <a href="catalog.php" class="btn btn-primary"><?= icon('cart', 18) ?><span>За покупками</span></a>
                    <a href="?tab=edit" class="btn btn-ghost"><?= icon('pencil', 17) ?><span>Редактировать</span></a>
                </div>
            </div>

            <div class="profile-counts">
                <a class="count-cell" href="?tab=orders">
                    <span class="count-num"><span class="count" data-count="<?= $ordersCount ?>">0</span></span>
                    <span class="count-lbl">заказов</span>
                </a>
                <a class="count-cell" href="?tab=orders">
                    <span class="count-num"><span class="count" data-count="<?= $unitsCount ?>">0</span></span>
                    <span class="count-lbl">товаров куплено</span>
                </a>
                <div class="count-cell">
                    <span class="count-num"><?= number_format($spentTotal, 0, '.', ' ') ?> ₽</span>
                    <span class="count-lbl">потрачено всего</span>
                </div>
                <div class="count-cell">
                    <span class="count-num"><span class="count" data-count="<?= $sinceDays ?>">0</span></span>
                    <span class="count-lbl">дней с нами</span>
                </div>
            </div>
        </div>

        <!-- Вкладки: просмотр / заказы / редактирование -->
        <div class="tabs fade-up d1">
            <a href="?tab=profile" class="<?= $tab === 'profile' ? 'active' : '' ?>">Профиль</a>
            <a href="?tab=orders"  class="<?= $tab === 'orders'  ? 'active' : '' ?>">Мои заказы<span class="cnt"><?= $ordersCount ?></span></a>
            <a href="?tab=edit"    class="<?= $tab === 'edit'    ? 'active' : '' ?>">Редактирование</a>
            <span class="tab-line" aria-hidden="true"></span>
        </div>

        <?php if (!empty($msg)): ?><div class="alert ok"><?= e($msg) ?></div><?php endif; ?>
        <?php if (!empty($err)): ?><div class="alert err"><?= e($err) ?></div><?php endif; ?>

        <?php if ($tab === 'profile'): ?>
            <div class="tab-panel profile-cols">
                <div class="col-main">
                    <div class="card-box">
                        <h2><?= icon('user', 20) ?><span>О себе</span></h2>
                        <div class="info-rows">
                            <div class="row"><span>Статус</span><span class="v<?= $user['status'] ? '' : ' is-empty' ?>"><?= e($user['status'] ?: 'не заполнен') ?></span></div>
                            <div class="row"><span>E-mail</span><span class="v<?= $user['email'] ? '' : ' is-empty' ?>"><?= e($user['email'] ?: 'не привязан') ?></span></div>
                            <div class="row"><span>Телефон</span><span class="v<?= $user['phone'] ? '' : ' is-empty' ?>"><?= e($user['phone'] ?: 'не указан') ?></span></div>
                            <div class="row"><span>Адрес доставки</span><span class="v<?= $user['address'] ? '' : ' is-empty' ?>"><?= e($user['address'] ?: 'не указан') ?></span></div>
                        </div>
                        <p class="hint" style="margin-top:14px">Эти поля меняются во вкладке «Редактирование».</p>
                    </div>

                    <?php if ($orders): ?>
                        <div class="card-box">
                            <h2><?= icon('box', 20) ?><span>Последние заказы</span></h2>
                            <?php foreach (array_slice($orders, 0, 3) as $o): $items = $itemsByOrder[$o['id']] ?? []; ?>
                                <div class="wall-card">
                                    <div class="wall-head">
                                        <span class="wall-avatar"><?= icon('cart', 19) ?></span>
                                        <span class="who-mini"><b>Заказ №<?= (int)$o['id'] ?></b> · <?= count($items) ?> поз.</span>
                                        <span class="wall-time"><?= date('d.m.Y H:i', strtotime($o['created_at'])) ?></span>
                                    </div>
                                    <div class="wall-body">
                                        <?= e(implode(', ', array_map(fn($i) => $i['name'] . ' ×' . $i['quantity'], array_slice($items, 0, 3)))) ?>
                                        <?= count($items) > 3 ? ' и ещё ' . (count($items) - 3) : '' ?>
                                    </div>
                                    <div class="wall-foot">
                                        <span class="status <?= orderStatusClass($o['status']) ?>"><?= orderStatusLabel($o['status']) ?></span>
                                        <span class="grow"></span>
                                        <span><b><?= number_format($o['total'], 2, '.', '') ?> ₽</b></span>
                                        <a href="?tab=orders#order-<?= (int)$o['id'] ?>">подробнее</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <a href="?tab=orders" class="btn btn-ghost btn-block">Показать все заказы</a>
                        </div>
                    <?php else: ?>
                        <div class="empty">
                            <div class="e-ic"><?= icon('cart', 34) ?></div>
                            <h3>Заказов пока нет</h3>
                            <p>Соберите корзину — привезём за 45 минут.</p>
                            <a href="catalog.php" class="btn btn-primary">В магазины</a>
                        </div>
                    <?php endif; ?>
                </div>

                <aside class="col-side">
                    <div class="card-box">
                        <h2><?= icon('tag', 19) ?><span>Корзина</span></h2>
                        <div class="info-rows">
                            <div class="row"><span>Позиций сейчас</span><span class="v"><?= (int)array_sum($_SESSION['cart']) ?></span></div>
                            <div class="row"><span>На сумму</span><span class="v"><?= number_format(cartTotal($pdo), 2, '.', '') ?> ₽</span></div>
                        </div>
                        <a href="cart.php" class="btn btn-ghost btn-block" style="margin-top:16px">Открыть корзину</a>
                    </div>

                    <div class="card-box">
                        <h2><?= icon('lock', 19) ?><span>Аккаунт</span></h2>
                        <div class="info-rows">
                            <div class="row"><span>Номер</span><span class="v">#<?= (int)$user['id'] ?></span></div>
                            <div class="row"><span>Регистрация</span><span class="v"><?= date('d.m.Y', strtotime($user['created_at'])) ?></span></div>
                            <div class="row"><span>Пароль</span><span class="v">установлен</span></div>
                        </div>
                        <a href="?tab=edit#password" class="btn btn-quiet btn-block" style="margin-top:12px">Сменить пароль</a>
                    </div>

                    <div class="card-box">
                        <h2><?= icon('pin', 19) ?><span>Сервис</span></h2>
                        <div class="info-rows">
                            <div class="row"><span>Зона доставки</span><span class="v"><a href="map.php">Нефтеюганск</a></span></div>
                            <div class="row"><span>Поддержка</span><span class="v">8 (3463) 00-00-00</span></div>
                        </div>
                        <a href="logout.php" class="btn btn-quiet btn-block" style="margin-top:12px">Выйти из аккаунта</a>
                    </div>
                </aside>
            </div>

        <?php elseif ($tab === 'orders'): ?>

            <div class="tab-panel">
                <?php if (!$orders): ?>
                    <div class="empty">
                        <div class="e-ic"><?= icon('cart', 34) ?></div>
                        <h3>Заказов пока нет</h3>
                        <p>Соберите корзину — привезём за 45 минут.</p>
                        <a href="catalog.php" class="btn btn-primary">В магазины</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                        <div class="order-card fade-up" id="order-<?= (int)$o['id'] ?>">
                            <div class="head">
                                <div>
                                    <div class="t">Заказ №<?= (int)$o['id'] ?></div>
                                    <div class="date"><?= date('d.m.Y H:i', strtotime($o['created_at'])) ?> · <?= $o['payment_method'] ? e($o['payment_method']) : 'СБП' ?></div>
                                </div>
                                <div class="right">
                                    <span class="status <?= orderStatusClass($o['status']) ?>"><?= orderStatusLabel($o['status']) ?></span>
                                    <span class="sum"><?= number_format($o['total'], 2, '.', '') ?> ₽</span>
                                    <span class="chev"><?= icon('chevron', 18) ?></span>
                                </div>
                            </div>
                            <div class="body-wrap">
                                <div class="body">
                                    <div class="body-inner">
                                        <?php foreach ($itemsByOrder[$o['id']] ?? [] as $it): ?>
                                            <div class="order-item">
                                                <span><?= e($it['name']) ?> <span class="q">× <?= (int)$it['quantity'] ?></span></span>
                                                <span class="p"><?= number_format($it['price'] * $it['quantity'], 2, '.', '') ?> ₽</span>
                                            </div>
                                        <?php endforeach; ?>
                                        <div class="order-foot">
                                            <span class="order-addr">
                                                <?= $o['address'] ? 'адрес: ' . e($o['address']) : 'адрес не указан' ?>
                                                <?= $o['delivery_slot'] ? ' · интервал: ' . e($o['delivery_slot']) : '' ?>
                                            </span>
                                            <span style="display:flex; gap:8px; flex-wrap:wrap">
                                                <button class="btn btn-ghost btn-sm" data-stub="Демо-режим: повтор заказа будет доступен позже">Повторить заказ</button>
                                                <?php if ($o['status'] === 'pending'): ?>
                                                    <a class="btn btn-primary btn-sm" href="payment.php?order=<?= (int)$o['id'] ?>">Оплатить</a>
                                                <?php elseif ($o['status'] === 'paid'): ?>
                                                    <a class="btn btn-primary btn-sm" href="?tab=orders&done=<?= (int)$o['id'] ?>">Подтвердить получение</a>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        <?php else: ?>

            <div class="tab-panel">
                <div class="card-box">
                    <h2><?= icon('camera', 20) ?><span>Фотография</span></h2>
                    <form method="post" enctype="multipart/form-data" action="profile.php?tab=edit" class="avatar-edit">
                        <input type="hidden" name="act" value="avatar">
                        <div class="prev" id="avatarPrev">
                            <?php if ($user['avatar']): ?>
                                <img src="<?= e($user['avatar']) ?>" alt="">
                            <?php else: ?>
                                <?= icon('user', 34) ?>
                            <?php endif; ?>
                        </div>
                        <div style="flex:1; min-width:200px">
                            <label class="btn btn-ghost file-btn">
                                <input type="file" name="avatar" id="avatarFile" accept="image/jpeg,image/png,image/webp" required>
                                <?= $user['avatar'] ? 'Выбрать новое фото' : 'Выбрать фото' ?>
                            </label>
                            <span class="hint" style="display:block;margin-top:8px">JPG, PNG или WebP · до 2 МБ · лучше квадратная</span>
                        </div>
                        <button class="btn btn-primary" type="submit">Сохранить фото</button>
                    </form>
                </div>

                <div class="card-box">
                    <h2><?= icon('pencil', 20) ?><span>Данные профиля</span></h2>
                    <form method="post" action="profile.php?tab=edit">
                        <input type="hidden" name="act" value="profile">
                        <div class="f-row">
                            <div class="f">
                                <label>Имя</label>
                                <input name="name" required value="<?= e($user['name']) ?>">
                            </div>
                            <div class="f">
                                <label>Телефон</label>
                                <input type="tel" name="phone" value="<?= e($user['phone']) ?>" placeholder="+7 (___) ___-__-__">
                            </div>
                        </div>
                        <div class="f">
                            <label>Статус <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--ink-3)">— что-нибудь о себе</span></label>
                            <input name="status" maxlength="120" value="<?= e($user['status']) ?>" placeholder="Например: вечно голодный к вечеру">
                        </div>
                        <div class="f">
                            <label>Адрес доставки</label>
                            <input name="address" value="<?= e($user['address']) ?>" placeholder="ул. Дружбы Народов, 15, кв. 42">
                            <div class="hint">Подставится автоматически при оформлении заказа.</div>
                        </div>
                        <button class="btn btn-primary" type="submit">Сохранить изменения</button>
                    </form>
                </div>

                <div class="card-box" id="password">
                    <h2><?= icon('lock', 20) ?><span>Смена пароля</span></h2>
                    <form method="post" action="profile.php?tab=edit">
                        <input type="hidden" name="act" value="password">
                        <div class="f-row">
                            <div class="f">
                                <label>Текущий пароль</label>
                                <input type="password" name="current" required autocomplete="current-password">
                            </div>
                            <div class="f">
                                <label>Новый пароль</label>
                                <input type="password" name="new" required id="regPass" autocomplete="new-password">
                            </div>
                        </div>
                        <div class="pass-hint" id="passHint">
                            <span class="req" data-check="length">6+ символов</span>
                            <span class="req" data-check="upper">A–Z</span>
                            <span class="req" data-check="lower">a–z</span>
                            <span class="req" data-check="digit">0–9</span>
                            <span class="req" data-check="special">!@#$</span>
                        </div>
                        <div class="f">
                            <label>Повторите новый пароль</label>
                            <input type="password" name="new2" required autocomplete="new-password">
                        </div>
                        <button class="btn btn-ghost" type="submit">Изменить пароль</button>
                    </form>
                </div>

                <a href="?tab=profile" class="btn btn-quiet">Вернуться к профилю</a>
            </div>

        <?php endif; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
