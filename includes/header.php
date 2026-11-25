<?php
require_once __DIR__.'/../config.php';
require_once __DIR__.'/icons.php';
$cssFile = __DIR__.'/../assets/style.css';
$jsFile  = __DIR__.'/../assets/app.js';
$cssVer  = file_exists($cssFile) ? filemtime($cssFile) : time();
$jsVer   = file_exists($jsFile)  ? filemtime($jsFile)  : time();
$page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'DesSega&') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,500;1,600&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=<?= $cssVer ?>">
</head>
<body>

<header class="site-header">
    <div class="wrap">
        <a href="index.php" class="logo">
            DesSega<span class="amp">&amp;</span>
        </a>
        <nav class="nav">
            <a href="catalog.php" <?= $page==='catalog' ? 'class="active"' : '' ?>><?= icon('cart', 17) ?><span>Магазины</span></a>
            <a href="map.php" <?= $page==='map' ? 'class="active"' : '' ?>><?= icon('pin', 17) ?><span>Доставка</span></a>
            <a href="cart.php" <?= $page==='cart' ? 'class="active"' : '' ?>><?= icon('box', 17) ?><span>Корзина</span><?php if (cartCount()): ?><span class="cart-pill"><?= cartCount() ?></span><?php endif; ?></a>
            <?php if (isAuth()): ?>
                <?php if (($_SESSION['role'] ?? 'user') === 'admin'): ?>
                    <a href="account.php" <?= $page==='account' ? 'class="active"' : '' ?>>
                        <span style="color:var(--brand);"><?= icon('lock', 17) ?></span><span>Панель создателя</span>
                    </a>
                <?php endif; ?>
                <a href="profile.php" <?= $page==='profile' ? 'class="active"' : '' ?>>
                    <span class="nav-avatar">
                        <?php if (!empty($_SESSION['user_avatar'])): ?>
                            <img src="<?= e($_SESSION['user_avatar']) ?>" alt="">
                        <?php else: ?>
                            <?= mb_strtoupper(mb_substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
                        <?php endif; ?>
                    </span><span>Профиль</span>
                </a>
                <a href="logout.php" class="nav-quiet">Выйти</a>
            <?php else: ?>
                <a href="login.php" <?= $page==='login' ? 'class="active"' : '' ?>>Войти</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main style="flex:1; display:flex; flex-direction:column;">
