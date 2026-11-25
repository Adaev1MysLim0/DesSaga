<?php
$title = 'Вакансии — DesSega&';
include 'includes/header.php';

$sent = isset($_GET['sent']);
$err  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $phone = normalizePhone($_POST['phone'] ?? '');
    $selfEmployed = isset($_POST['self_employed']) ? 1 : 0;

    if (mb_strlen($name) < 2) {
        $err = 'Укажите ФИО полностью.';
    } elseif (!$phone) {
        $err = 'Укажите корректный телефон.';
    } else {
        // Загрузка фото паспорта
        $photoPath = null;
        if (!empty($_FILES['passport']['name']) && $_FILES['passport']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['passport']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                if (!is_dir(__DIR__ . '/uploads/passports')) {
                    mkdir(__DIR__ . '/uploads/passports', 0755, true);
                }
                $photoPath = 'uploads/passports/p' . time() . mt_rand(1000, 9999) . '.' . $ext;
                move_uploaded_file($_FILES['passport']['tmp_name'], __DIR__ . '/' . $photoPath);
            }
        }
        $stmt = $pdo->prepare("INSERT INTO courier_applications(name, phone, passport_photo, self_employed) VALUES(?,?,?,?)");
        $stmt->execute([$name, $phone, $photoPath, $selfEmployed]);
        header('Location: careers.php?sent=1'); exit;
    }
}
?>

<section class="page">
    <div class="wrap">
        <div class="page-head reveal">
            <div class="eyebrow">Вакансии</div>
            <h1>Стань частью DesSega&</h1>
            <p class="sub">Открытая вакансия с гибким графиком и ежедневными выплатами.</p>
        </div>

        <?php if ($sent): ?>
            <div class="alert ok reveal" style="margin-bottom:22px;">
                <strong>✓ Заявка отправлена!</strong> Мы свяжемся с вами в течение 2 рабочих дней.
            </div>
        <?php endif; ?>
        <?php if ($err): ?>
            <div class="alert err reveal" style="margin-bottom:22px;"><?= e($err) ?></div>
        <?php endif; ?>

        <!-- Карточка вакансии -->
        <div class="card-box reveal" style="margin-bottom:22px;">
            <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:18px;">
                <div style="width:52px;height:52px;border-radius:14px;background:var(--brand-soft);color:var(--brand-ink);display:flex;align-items:center;justify-content:center;"><?= icon('truck', 26) ?></div>
                <div style="flex:1;min-width:200px;">
                    <h2 style="margin:0;font-size:24px;">Курьер</h2>
                    <div style="color:var(--ink-3);font-size:13.5px;margin-top:2px;">DesSega& · Нефтеюганск</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-family:var(--serif);font-size:24px;font-weight:700;color:var(--brand);">от 2 500 ₽<span style="font-size:13px;color:var(--ink-3);font-family:var(--sans);font-weight:400;">/смена</span></div>
                    <span class="status st-done" style="margin-top:4px;">Открыта</span>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px;">
                <div style="background:var(--paper-2);border-radius:12px;padding:16px;">
                    <div style="font-size:12px;color:var(--ink-3);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">График</div>
                    <div style="font-size:14.5px;font-weight:600;">Свободный — слоты 4–12 часов</div>
                </div>
                <div style="background:var(--paper-2);border-radius:12px;padding:16px;">
                    <div style="font-size:12px;color:var(--ink-3);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Выплаты</div>
                    <div style="font-size:14.5px;font-weight:600;">Ежедневно на карту</div>
                </div>
                <div style="background:var(--paper-2);border-radius:12px;padding:16px;">
                    <div style="font-size:12px;color:var(--ink-3);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Формат</div>
                    <div style="font-size:14.5px;font-weight:600;">Пеший / вело — экипировка предоставляется</div>
                </div>
                <div style="background:var(--paper-2);border-radius:12px;padding:16px;">
                    <div style="font-size:12px;color:var(--ink-3);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Опыт</div>
                    <div style="font-size:14.5px;font-weight:600;">Не требуется — научим за день</div>
                </div>
            </div>
        </div>

        <!-- Форма регистрации курьера (как в Яндекс Еде: данные + паспорт + самозанятость) -->
        <div class="card-box reveal">
            <h2><?= icon('pencil', 20) ?> Регистрация курьера</h2>
            <p class="hint" style="margin:-10px 0 22px;">Заполните три шага — и уже завтра сможете выйти на первую смену.</p>

            <form method="post" enctype="multipart/form-data">
                <!-- Шаг 1: данные -->
                <div style="font-size:12.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--brand);margin-bottom:14px;">Шаг 1 · Ваши данные</div>
                <div class="f-row">
                    <div class="f">
                        <label>ФИО</label>
                        <input name="name" required value="<?= e($_POST['name'] ?? '') ?>" placeholder="Иванов Иван Иванович">
                    </div>
                    <div class="f">
                        <label>Телефон</label>
                        <input type="tel" name="phone" required value="<?= e($_POST['phone'] ?? '') ?>" placeholder="+7 (___) ___-__-__">
                    </div>
                </div>

                <!-- Шаг 2: паспорт -->
                <div style="font-size:12.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--brand);margin:22px 0 14px;">Шаг 2 · Паспорт по фото</div>
                <div class="f">
                    <label>Фото разворота паспорта с пропиской</label>
                    <div class="avatar-edit">
                        <div class="prev" id="passportPrev"><?= icon('camera', 26) ?></div>
                        <div>
                            <label class="btn btn-ghost file-btn" style="cursor:pointer;">
                                <?= icon('arrow', 16) ?><span>Загрузить фото</span>
                                <input type="file" name="passport" id="passportFile" accept="image/jpeg,image/png,image/webp">
                            </label>
                            <div class="hint" style="margin-top:8px;">JPG, PNG или WEBP. Фото должно быть чётким, без бликов.</div>
                        </div>
                    </div>
                </div>

                <!-- Шаг 3: самозанятость -->
                <div style="font-size:12.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--brand);margin:22px 0 14px;">Шаг 3 · Оформление</div>
                <label class="opt">
                    <input type="checkbox" name="self_employed" value="1" <?= !empty($_POST['self_employed']) ? 'checked' : '' ?>>
                    <div>
                        <div class="tt">Я самозанятый</div>
                        <div class="dd">Есть статус в приложении «Мой налог» — чеки формируем автоматически</div>
                    </div>
                </label>
                <label class="opt">
                    <input type="radio" name="reg_kind" value="help" checked>
                    <div>
                        <div class="tt">Нужна помощь с оформлением самозанятости</div>
                        <div class="dd">Поможем зарегистрироваться за 15 минут в день выхода на смену</div>
                    </div>
                </label>

                <button class="btn btn-primary btn-lg btn-block" type="submit" style="margin-top:22px;">
                    <?= icon('send', 18) ?><span>Отправить заявку</span>
                </button>
                <div class="hint" style="text-align:center;margin-top:12px;">Отправляя заявку, вы соглашаетесь с обработкой персональных данных.</div>
            </form>
        </div>
    </div>
</section>

<script>
// Превью фото паспорта до загрузки
(function() {
    var f = document.getElementById('passportFile');
    var p = document.getElementById('passportPrev');
    if (f && p) {
        f.addEventListener('change', function() {
            var file = f.files && f.files[0];
            if (!file) return;
            p.innerHTML = '<img src="' + URL.createObjectURL(file) + '" alt="" style="width:100%;height:100%;object-fit:cover;">';
        });
    }
})();
</script>

<?php include 'includes/footer.php'; ?>
