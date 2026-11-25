<?php
require_once 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $id = (int)$_POST['add'];
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
    header('Location: catalog.php' . (($_GET['q'] ?? '') !== '' ? '?q=' . urlencode($_GET['q']) : ''));
    exit;
}

$q   = trim($_GET['q'] ?? '');
$cat = (int)($_GET['cat'] ?? 0);

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY id")->fetchAll();

$where = []; $params = [];
if ($q !== '')   { $where[] = "(p.name LIKE ? OR p.description LIKE ?)"; $params[] = "%$q%"; $params[] = "%$q%"; }
if ($cat > 0)    { $where[] = "p.category_id = ?"; $params[] = $cat; }
$sql = "SELECT p.*, c.name AS cat FROM products p JOIN categories c ON c.id=p.category_id";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY c.id, p.id";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$title = 'Магазины'; include 'includes/header.php';
?>
<div class="page">
    <div class="wrap">
        <div class="page-head fade-up">
            <h1>Магазины · Магнит</h1>
            <p class="sub">Товары партнёра со скидками — собирайте корзину, привезём за 45 минут.</p>
        </div>

        <form method="get" class="toolbar fade-up d1">
            <div class="search">
                <input name="q" value="<?= e($q) ?>" placeholder="Найти среди <?= count($products) ?> товаров…" autocomplete="off">
                <span class="search-ic"><?= icon('search', 18) ?></span>
            </div>
            <?php if ($q !== ''): ?><input type="hidden" name="cat" value="<?= $cat ?>"><?php endif; ?>
            <button class="btn btn-ghost" type="submit">Найти</button>
        </form>

        <div class="chips fade-up d2" style="margin-bottom:28px">
            <a class="chip <?= $cat === 0 ? 'active' : '' ?>" href="catalog.php<?= $q !== '' ? '?q=' . urlencode($q) : '' ?>">Все</a>
            <?php foreach ($categories as $c): ?>
                <a class="chip <?= $cat === (int)$c['id'] ? 'active' : '' ?>"
                   href="catalog.php?cat=<?= $c['id'] ?><?= $q !== '' ? '&q=' . urlencode($q) : '' ?>"><?= e($c['name']) ?></a>
            <?php endforeach; ?>
        </div>

        <?php if (!$products): ?>
            <div class="empty">
                <div class="e-ic"><?= icon('search', 32) ?></div>
                <h3>Ничего не нашлось</h3>
                <p>Попробуйте другой запрос или снимите фильтр.</p>
                <a href="catalog.php" class="btn btn-ghost">Сбросить поиск</a>
            </div>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($products as $p): ?>
                    <div class="product reveal">
                        <div class="ph">
                            <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy" onerror="this.style.opacity=0">
                            <?php if ($p['old_price']): ?>
                                <span class="badge">-<?= round(100 - $p['price'] / $p['old_price'] * 100) ?>%</span>
                            <?php endif; ?>
                        </div>
                        <div class="pb">
                            <div class="cat"><?= e($p['cat']) ?></div>
                            <div class="name"><?= e($p['name']) ?></div>
                            <div class="desc"><?= e($p['description']) ?></div>
                            <div class="row">
                                <span class="price"><?= number_format($p['price'], 2, '.', '') ?> ₽</span>
                                <?php if ($p['old_price']): ?>
                                    <span class="old"><?= number_format($p['old_price'], 2, '.', '') ?> ₽</span>
                                <?php endif; ?>
                            </div>
                            <form method="post">
                                <input type="hidden" name="add" value="<?= $p['id'] ?>">
                                <button class="btn btn-primary btn-block" type="submit">В корзину</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
