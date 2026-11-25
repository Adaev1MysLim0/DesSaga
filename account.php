<?php
require_once 'config.php';

// Доступ только для создателя (admin)
if (!isAuth() || ($_SESSION['role'] ?? 'user') !== 'admin') {
    header('Location: login.php'); exit;
}

// Действия: удалить / пометить прочитанным
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM support_messages WHERE id=?")->execute([(int)$_GET['delete']]);
    header('Location: account.php'); exit;
}
if (isset($_GET['read'])) {
    $pdo->prepare("UPDATE support_messages SET status='read' WHERE id=?")->execute([(int)$_GET['read']]);
    header('Location: account.php'); exit;
}

// Сообщения из поддержки (приходят на аккаунт создателя)
$messages = $pdo->query(
    "SELECT sm.*, u.name AS user_name FROM support_messages sm
     LEFT JOIN users u ON sm.user_id = u.id
     ORDER BY sm.created_at DESC"
)->fetchAll();
$unread = count(array_filter($messages, fn($m) => $m['status'] === 'pending'));

// Заявки курьеров
$couriers = $pdo->query("SELECT * FROM courier_applications ORDER BY created_at DESC")->fetchAll();

$title = 'Панель создателя — DesSega&';
include 'includes/header.php';
?>
<style>
    .admin-badge {
        display:inline-flex; align-items:center; gap:6px; padding:4px 12px; border-radius:99px;
        background:var(--brand); color:#1d1410; font-size:12px; font-weight:700;
    }
    .msg-card {
        background:var(--card); border:1px solid var(--line); border-radius:var(--radius);
        padding:22px 24px; margin-bottom:16px;
        transition:border-color .3s var(--ease), transform .3s var(--ease), box-shadow .3s var(--ease);
    }
    .msg-card:hover { border-color:var(--line-2); transform:translateY(-2px); box-shadow:var(--shadow-s); }
    .msg-card.unread { border-left:3px solid var(--brand); }
    .msg-head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; margin-bottom:12px; }
    .msg-meta { font-size:12.5px; color:var(--ink-3); margin-top:2px; }
    .msg-text { background:var(--paper-2); border-radius:12px; padding:14px 18px; font-size:14.5px; color:var(--ink-2); line-height:1.6; }
    .msg-actions { display:flex; gap:8px; flex-wrap:wrap; margin-top:14px; }
    .reply-box { margin-top:12px; padding:14px; background:var(--green-soft); border-radius:12px; font-size:14px; color:var(--green); line-height:1.5; }
    .cnt-pill { font-family:var(--sans); font-size:12px; background:var(--brand-soft); color:var(--brand-ink); padding:2px 10px; border-radius:99px; }
</style>

<section class="page">
    <div class="wrap">
        <div class="page-head">
            <div class="eyebrow">Личный кабинет создателя</div>
            <h1 style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                Панель создателя
                <span class="admin-badge"><?= icon('check', 14) ?> ADMIN</span>
            </h1>
            <p class="sub">
                Привет, <b style="color:var(--ink);"><?= e($_SESSION['user_name']) ?></b>!
                Здесь приходят обращения пользователей из поддержки.
                <?php if ($unread): ?>Непрочитанных: <b style="color:var(--brand-ink);"><?= $unread ?></b><?php endif; ?>
            </p>
        </div>

        <!-- Обращения в поддержку -->
        <div class="card-box">
            <h2><?= icon('chat', 20) ?> Сообщения поддержки <span class="cnt-pill"><?= count($messages) ?></span></h2>

            <?php if (empty($messages)): ?>
                <div class="empty">
                    <div class="e-ic"><?= icon('chat', 32) ?></div>
                    <h3>Пока нет обращений</h3>
                    <p>Здесь появятся сообщения пользователей, написавших в <a href="support.php" style="color:var(--brand-ink);">Поддержку</a>.</p>
                </div>
            <?php else: foreach ($messages as $msg): ?>
                <div class="msg-card <?= $msg['status'] === 'pending' ? 'unread' : '' ?>">
                    <div class="msg-head">
                        <div>
                            <div style="font-weight:600;font-size:16px;color:var(--ink);">
                                <?= e($msg['name']) ?>
                                <?php if ($msg['user_name'] && $msg['name'] !== $msg['user_name']): ?>
                                    <span style="color:var(--ink-3);font-weight:400;font-size:13px;">(аккаунт: <?= e($msg['user_name']) ?>)</span>
                                <?php endif; ?>
                            </div>
                            <div class="msg-meta">
                                <?= $msg['email'] ? e($msg['email']) . ' · ' : '' ?><?= e($msg['created_at']) ?>
                            </div>
                        </div>
                        <span class="status <?= $msg['status'] === 'pending' ? 'st-pending' : 'st-done' ?>">
                            <?= $msg['status'] === 'pending' ? 'Новое' : 'Прочитано' ?>
                        </span>
                    </div>
                    <div class="msg-text"><?= nl2br(e($msg['message'])) ?></div>
                    <?php if ($msg['reply']): ?>
                        <div class="reply-box"><strong>Бот ответил:</strong> <?= e($msg['reply']) ?></div>
                    <?php endif; ?>
                    <div class="msg-actions">
                        <?php if ($msg['status'] === 'pending'): ?>
                            <a href="account.php?read=<?= (int)$msg['id'] ?>" class="btn btn-sm btn-ghost"><?= icon('check', 14) ?><span>Прочитано</span></a>
                        <?php endif; ?>
                        <a href="account.php?delete=<?= (int)$msg['id'] ?>" class="btn btn-sm btn-quiet" onclick="return confirm('Удалить обращение?');"><?= icon('close', 14) ?><span>Удалить</span></a>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>

        <!-- Заявки курьеров -->
        <div class="card-box">
            <h2><?= icon('truck', 20) ?> Заявки курьеров <span class="cnt-pill"><?= count($couriers) ?></span></h2>

            <?php if (empty($couriers)): ?>
                <div class="empty">
                    <div class="e-ic"><?= icon('truck', 32) ?></div>
                    <h3>Заявок нет</h3>
                    <p>Анкеты курьеров с <a href="careers.php" style="color:var(--brand-ink);">вкладки Вакансии</a> появятся здесь.</p>
                </div>
            <?php else: ?>
                <div class="info-rows">
                    <?php foreach ($couriers as $c): ?>
                        <div class="row" style="align-items:flex-start;">
                            <span>
                                <b style="color:var(--ink);font-weight:600;"><?= e($c['name']) ?></b><br>
                                <span style="font-size:12px;"><?= e($c['phone']) ?> · <?= e($c['created_at']) ?></span><br>
                                <span style="font-size:12px;color:<?= $c['self_employed'] ? 'var(--green)' : 'var(--amber)' ?>;">
                                    <?= $c['self_employed'] ? 'Самозанятый' : 'Нужно оформить самозанятость' ?>
                                    <?php if ($c['passport_photo']): ?>
                                        · <a href="<?= e($c['passport_photo']) ?>" target="_blank" style="color:var(--brand-ink);">фото паспорта</a>
                                    <?php endif; ?>
                                </span>
                            </span>
                            <span class="v"><span class="status st-pending" style="font-size:11.5px;">Новая</span></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
