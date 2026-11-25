</main>

<footer class="site-footer">
    <div class="wrap">
        <div class="cols">
            <div>
                <div class="f-logo">DesSega<span class="amp">&amp;</span></div>
                <p class="f-desc">
                    Сервис доставки продуктов из сети «Магнит» по Нефтеюганску.
                    Заказ за минуту, доставка за 45 минут, оплата по СБП.
                </p>
            </div>
            <div>
                <h4>Сервис</h4>
                <ul>
                    <li><a href="catalog.php">Магазины</a></li>
                    <li><a href="cart.php">Корзина</a></li>
                    <li><a href="map.php">Зона доставки</a></li>
                    <li><a href="profile.php">Личный кабинет</a></li>
                </ul>
            </div>
            <div>
                <h4>Компания</h4>
                <ul>
                    <li><a href="about.php" <?= $page==='about' ? 'class="active"' : '' ?>>О нас</a></li>
                    <li><a href="partners.php" <?= $page==='partners' ? 'class="active"' : '' ?>>Партнёрам</a></li>
                    <li><a href="support.php" <?= $page==='support' ? 'class="active"' : '' ?>>Поддержка</a></li>
                    <li><a href="careers.php" <?= $page==='careers' ? 'class="active"' : '' ?>>Вакансии</a></li>
                </ul>
            </div>
        </div>
        <div class="bottom">
            <span>© <?= date('Y') ?> <b>DesSega&amp;</b> — демо-проект. Оплата, SMS и пуши — заглушки.</span>
            <span>Нефтеюганск · <b>8 (3463) 00-00-00</b> · info@dessega.ru</span>
        </div>
    </div>
</footer>

<script src="assets/app.js?v=<?= $jsVer ?>"></script>
</body>
</html>
