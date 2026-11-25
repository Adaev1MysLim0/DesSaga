<?php
$title = 'О нас — DesSega&';
include 'includes/header.php';
?>

<section class="page">
    <div class="wrap">
        <div class="page-head reveal">
            <div class="eyebrow">О нас</div>
            <h1>DesSega& — доставка, которая работает</h1>
            <p class="sub">Мы — команда из Нефтеюганска, которая делает продуктовую доставку быстрой, дешёвой и без лишних хлопот.</p>
        </div>

        <!-- Команда -->
        <div class="card-box reveal" style="margin-bottom:22px">
            <h2><?= icon('people', 20) ?> Команда</h2>
            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap:22px; margin-top:6px;">
                <!-- Создатель -->
                <div class="feature-card" style="text-align:center">
                    <div style="width:72px;height:72px;border-radius:50%;background:var(--brand-soft);color:var(--brand-ink);font-family:var(--serif);font-size:26px;font-weight:700;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;transition:transform .3s var(--ease);">XM</div>
                    <h3>Адаев Муслим</h3>
                    <p style="color:var(--brand);font-weight:600;font-size:13px;margin-bottom:8px;">основатель</p>
                    <p>Создатель и руководитель проекта. Отвечает за архитектуру, разработку и стратегию развития DesSega&.</p>
                </div>
                <!-- Дизайнер -->
                <div class="feature-card" style="text-align:center">
                    <div style="width:72px;height:72px;border-radius:50%;background:var(--brand-soft);color:var(--brand-ink);font-family:var(--serif);font-size:26px;font-weight:700;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;transition:transform .3s var(--ease);">XA</div>
                    <h3>Боярко Игорь</h3>
                    <p style="color:var(--brand);font-weight:600;font-size:13px;margin-bottom:8px;">дизайнер</p>
                    <p>Отвечает за визуальный стиль, UX/UI и бренд DesSega&. Каждый пиксель — продуман.</p>
                </div>
            </div>
        </div>

        <!-- Офис -->
        <div class="card-box reveal">
            <h2><?= icon('pin', 20) ?> Наш офис</h2>
            <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
                <div style="flex:1;min-width:250px;">
                    <p style="color:var(--ink-2);font-size:15px;margin-bottom:10px;">Приходите, если есть идеи, вопросы или предложения — мы всегда рады живому общению.</p>
                    <div class="info-rows">
                        <div class="row"><span>Город</span><span class="v">Нефтеюганск</span></div>
                        <div class="row"><span>Адрес</span><span class="v">ул. 11 В, дом 19</span></div>
                        <div class="row"><span>Кабинет</span><span class="v">425</span></div>
                        <div class="row"><span>Режим работы</span><span class="v">Пн–Пт 9:00–18:00</span></div>
                    </div>
                </div>
                <div style="flex:1;min-width:250px;height:200px;border-radius:12px;background:var(--paper-3);display:flex;align-items:center;justify-content:center;color:var(--ink-3);font-size:14px;border:1px solid var(--line);">
                    <span style="text-align:center">Карта<br><small>ул. 11 В, дом 19</small></span>
                </div>
            </div>
        </div>

        <!-- Факты -->
        <div class="card-box reveal" style="margin-top:22px">
            <h2><?= icon('star', 20) ?> Факты</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px;">
                <div style="text-align:center;padding:20px;background:var(--paper-2);border-radius:12px;">
                    <div style="font-family:var(--serif);font-size:32px;font-weight:700;color:var(--brand);">2024</div>
                    <div style="font-size:13px;color:var(--ink-3);margin-top:4px;">год основания</div>
                </div>
                <div style="text-align:center;padding:20px;background:var(--paper-2);border-radius:12px;">
                    <div style="font-family:var(--serif);font-size:32px;font-weight:700;color:var(--brand);">45 мин</div>
                    <div style="font-size:13px;color:var(--ink-3);margin-top:4px;">средняя доставка</div>
                </div>
                <div style="text-align:center;padding:20px;background:var(--paper-2);border-radius:12px;">
                    <div style="font-family:var(--serif);font-size:32px;font-weight:700;color:var(--brand);">31+</div>
                    <div style="font-size:13px;color:var(--ink-3);margin-top:4px;">товаров в каталоге</div>
                </div>
                <div style="text-align:center;padding:20px;background:var(--paper-2);border-radius:12px;">
                    <div style="font-family:var(--serif);font-size:32px;font-weight:700;color:var(--brand);">0 ₽</div>
                    <div style="font-size:13px;color:var(--ink-3);margin-top:4px;">доставка по городу</div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
