<?php
require_once 'config.php';
$err = '';
$method = ($_POST['method'] ?? 'email') === 'phone' ? 'phone' : 'email';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $pass = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';
    $email = ''; $phone = '';

    $errors = [];
    if (mb_strlen($name) < 2) $errors[] = 'Имя — минимум 2 символа';
    if ($pass !== $pass2)     $errors[] = 'Пароли не совпадают';
    if (strlen($pass) < 6)    $errors[] = 'Пароль — минимум 6 символов';
    if (!preg_match('/[A-Z]/', $pass))  $errors[] = 'в пароле нужна заглавная буква (A–Z)';
    if (!preg_match('/[a-z]/', $pass))  $errors[] = 'в пароле нужна строчная буква (a–z)';
    if (!preg_match('/[0-9]/', $pass))  $errors[] = 'в пароле нужна цифра (0–9)';
    if (!preg_match('/[^A-Za-z0-9]/', $pass)) $errors[] = 'в пароле нужен спецсимвол (!@#$…)';

    if ($method === 'phone') {
        $phone = normalizePhone($_POST['phone'] ?? '');
        if (!$phone) $errors[] = 'Укажите корректный телефон';
        // Заглушка SMS: в демо-режиме код всегда 1234
        if (trim($_POST['sms'] ?? '') !== '1234') $errors[] = 'Неверный код из SMS (демо-код: 1234)';
    } else {
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Укажите корректный e-mail';
    }

    if (empty($errors)) {
        if ($method === 'phone') {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE phone=?");
            $stmt->execute([$phone]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email=?");
            $stmt->execute([$email]);
        }
        if ($stmt->fetch()) {
            $err = 'Этот ' . ($method === 'phone' ? 'телефон' : 'e-mail') . ' уже зарегистрирован.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users(name,email,phone,password) VALUES(?,?,?,?)")
                ->execute([$name, $method === 'email' ? $email : null, $phone ?: null, $hash]);
            $_SESSION['user_id']    = $pdo->lastInsertId();
            $_SESSION['user_name']  = $name;
            $_SESSION['user_avatar'] = null;
            $_SESSION['role']       = 'user';
            session_regenerate_id(true);
            header('Location: profile.php'); exit;
        }
    } else {
        $err = 'Проверьте: ' . mb_strtolower(implode('; ', $errors)) . '.';
    }
}
$title = 'Регистрация'; include 'includes/header.php';
?>
<div class="page">
    <div class="wrap">
        <div class="auth-card fade-up">
            <h1>Создать аккаунт</h1>
            <p class="sub">Две минуты — и корзина доедет до вашей двери.</p>

            <?php if ($err): ?><div class="alert err"><?= e($err) ?></div><?php endif; ?>

            <form method="post" id="regForm">
                <input type="hidden" name="method" id="regMethod" value="<?= e($method) ?>">

                <div class="seg">
                    <button type="button" data-method="email" class="<?= $method==='email' ? 'on' : '' ?>">E-mail</button>
                    <button type="button" data-method="phone" class="<?= $method==='phone' ? 'on' : '' ?>">Телефон</button>
                </div>

                <div class="f">
                    <label>Как вас зовут</label>
                    <input name="name" required value="<?= e($_POST['name'] ?? '') ?>" placeholder="Например, Алекс">
                </div>

                <div id="emailFields" style="<?= $method==='phone' ? 'display:none' : '' ?>">
                    <div class="f">
                        <label>E-mail</label>
                        <input type="email" name="email" <?= $method==='phone' ? '' : 'required' ?>
                               value="<?= e($_POST['email'] ?? '') ?>" placeholder="you@example.com">
                    </div>
                </div>

                <div id="phoneFields" style="<?= $method==='phone' ? '' : 'display:none' ?>">
                    <div class="f">
                        <label>Телефон</label>
                        <input type="tel" name="phone" <?= $method==='phone' ? 'required' : '' ?>
                               value="<?= e($_POST['phone'] ?? '') ?>" placeholder="+7 (___) ___-__-__">
                        <div class="hint">SMS с кодом подтверждения — заглушка. Демо-код: <b>1234</b></div>
                    </div>
                    <div class="f">
                        <label>Код из SMS</label>
                        <input name="sms" inputmode="numeric" maxlength="4"
                               <?= $method==='phone' ? 'required' : '' ?>
                               value="<?= e($_POST['sms'] ?? '') ?>" placeholder="1234">
                    </div>
                </div>

                <div class="f">
                    <label>Пароль</label>
                    <input type="password" name="password" required id="regPass"
                           placeholder="Минимум 6 символов" autocomplete="new-password">
                </div>
                <div class="pass-hint" id="passHint">
                    <span class="req" data-check="length">6+ символов</span>
                    <span class="req" data-check="upper">A–Z</span>
                    <span class="req" data-check="lower">a–z</span>
                    <span class="req" data-check="digit">0–9</span>
                    <span class="req" data-check="special">!@#$</span>
                </div>
                <div class="f">
                    <label>Повторите пароль</label>
                    <input type="password" name="password2" required placeholder="Ещё раз" autocomplete="new-password">
                </div>

                <button class="btn btn-primary btn-block btn-lg" type="submit">Зарегистрироваться</button>
            </form>

            <p class="auth-alt">Уже есть аккаунт? <a href="login.php">Войти</a></p>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
