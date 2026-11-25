<?php
require_once 'config.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $pass  = $_POST['password'] ?? '';

    // ищем по e-mail, а если похоже на телефон — по нормализованному номеру
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=? OR phone=? LIMIT 1");
    $stmt->execute([$login, normalizePhone($login)]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password'])) {
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_avatar'] = $user['avatar'] ?? null;
        $_SESSION['role']       = $user['role'] ?? 'user';
        session_regenerate_id(true);
        header('Location: ' . (($_SESSION['role'] ?? '') === 'admin' ? 'account.php' : 'profile.php')); exit;
    }
    $err = 'Неверный e-mail/телефон или пароль.';
}
$title = 'Вход'; include 'includes/header.php';
?>
<div class="page">
    <div class="wrap">
        <div class="auth-card fade-up">
            <h1>С возвращением</h1>
            <p class="sub">Войдите, чтобы продолжить оформлять заказы.</p>

            <?php if ($err): ?><div class="alert err"><?= e($err) ?></div><?php endif; ?>

            <form method="post">
                <div class="f">
                    <label>E-mail или телефон</label>
                    <input name="login" required autofocus placeholder="you@example.com или +7…">
                </div>
                <div class="f">
                    <label>Пароль</label>
                    <input type="password" name="password" required placeholder="••••••" autocomplete="current-password">
                </div>
                <button class="btn btn-primary btn-block btn-lg" type="submit">Войти</button>
            </form>

            <p class="auth-alt">Нет аккаунта? <a href="register.php">Регистрация за 2 минуты</a></p>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
