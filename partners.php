<?php
$title = 'Партнёры — DesSega&';
include 'includes/header.php';
?>

<section class="page">
    <div class="wrap">
        <div class="page-head reveal">
            <div class="eyebrow">Партнёры</div>
            <h1>Нам доверяют</h1>
            <p class="sub">DesSega& работает вместе с ведущими компаниями — от крупных ритейлеров до технологических платформ.</p>
        </div>

        <div class="grid" data-stagger>
            <!-- Яндекс -->
            <div class="product">
                <div class="ph" style="background:linear-gradient(135deg,#ffcc00 0%,#ff9900 100%);">
                    <span style="font-size:48px;font-weight:800;color:#1a1a1a;font-family:var(--serif);">Я</span>
                </div>
                <div class="pb">
                    <div class="cat">Логистика и технологии</div>
                    <div class="name">Яндекс</div>
                    <div class="desc">Маршрутизация, навигация и аналитика доставки. Яндекс помогает нам находить самый быстрый путь к вашей двери.</div>
                    <div style="margin-top:auto;">
                        <span class="chip" style="pointer-events:none;">Партнёр</span>
                    </div>
                </div>
            </div>

            <!-- Магнит -->
            <div class="product">
                <div class="ph" style="background:linear-gradient(135deg,#e53935 0%,#c62828 100%);">
                    <span style="font-size:36px;font-weight:800;color:#fff;font-family:var(--sans);">М</span>
                </div>
                <div class="pb">
                    <div class="cat">Продуктовый ритейл</div>
                    <div class="name">Магнит</div>
                    <div class="desc">Основной поставщик продуктов. Свежие товары, реальные скидки до 30% и удобная зона самовывоза для наших курьеров.</div>
                    <div style="margin-top:auto;">
                        <span class="chip" style="pointer-events:none;">Основной</span>
                    </div>
                </div>
            </div>

            <!-- НПК -->
            <div class="product">
                <div class="ph" style="background:linear-gradient(135deg,#1565c0 0%,#0d47a1 100%);">
                    <span style="font-size:36px;font-weight:800;color:#fff;font-family:var(--sans);">Н</span>
                </div>
                <div class="pb">
                    <div class="cat">IT-решения</div>
                    <div class="name">НПК</div>
                    <div class="desc">Разработка и поддержка платформы. НПК обеспечивает стабильную работу сайта, базы данных и API интеграции.</div>
                    <div style="margin-top:auto;">
                        <span class="chip" style="pointer-events:none;">Технологии</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Статистика партнёрства -->
        <div class="card-box reveal" style="margin-top:22px">
            <h2><?= icon('star', 20) ?> Цифры партнёрства</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px;">
                <div style="text-align:center;padding:20px;background:var(--paper-2);border-radius:12px;">
                    <div style="font-family:var(--serif);font-size:32px;font-weight:700;color:var(--brand);">3</div>
                    <div style="font-size:13px;color:var(--ink-3);margin-top:4px;">ключевых партнёра</div>
                </div>
                <div style="text-align:center;padding:20px;background:var(--paper-2);border-radius:12px;">
                    <div style="font-family:var(--serif);font-size:32px;font-weight:700;color:var(--brand);">30%</div>
                    <div style="font-size:13px;color:var(--ink-3);margin-top:4px;">скидок от Магнита</div>
                </div>
                <div style="text-align:center;padding:20px;background:var(--paper-2);border-radius:12px;">
                    <div style="font-family:var(--serif);font-size:32px;font-weight:700;color:var(--brand);">24/7</div>
                    <div style="font-size:13px;color:var(--ink-3);margin-top:4px;">поддержка платформы</div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
