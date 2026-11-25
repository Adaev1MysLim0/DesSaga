<?php
require_once 'config.php';
$title = 'Зона доставки'; include 'includes/header.php';
?>
<div class="page">
    <div class="wrap">
        <div class="page-head fade-up">
            <h1>Куда мы возим</h1>
            <p class="sub">Центр карты — Нефтеюганск. Круг показывает зону бесплатной доставки.</p>
        </div>

        <div class="map-shell fade-up d1">
            <div id="ymap"></div>
        </div>

        <div class="map-cards">
            <div class="feature-card reveal">
                <div class="ic"><?= icon('truck', 22) ?></div>
                <h3>Вся зона — бесплатно</h3>
                <p>Доставляем по всем микрорайонам Нефтеюганска и Старого города в пределах 12 км от центра — без доплаты за километры.</p>
            </div>
            <div class="feature-card reveal">
                <div class="ic"><?= icon('clock', 22) ?></div>
                <h3>Пн–Вс · 09:00–22:00</h3>
                <p>Без выходных. Последние заказы принимаем в 21:15, чтобы курьер успел доехать до вашей двери.</p>
            </div>
            <div class="feature-card reveal">
                <div class="ic"><?= icon('phone', 22) ?></div>
                <h3>Мы на связи</h3>
                <p>8 (3463) 00-00-00 · info@dessega.ru<br>Курьер позвонит за 10 минут до прибытия.</p>
            </div>
        </div>
    </div>
</div>

<script src="https://api-maps.yandex.ru/2.1/?apikey=lite&lang=ru_RU"></script>
<script>
// Яндекс.Карты: центр — Нефтеюганск, зона доставки — круг 12 км.
// apikey=lite — бесплатной ключ «Яндекс Карт Плюс» для некоммерческого использования.
// Для продакшена получите свой ключ: https://developer.tech.yandex.ru/
(function () {
    var CITY = [61.0998, 76.7461]; // [широта, долгота]
    ymaps.ready(function () {
        var map = new ymaps.Map('ymap', {
            center: CITY,
            zoom: 11,
            controls: ['zoomControl', 'geolocationControl', 'typeControl']
        });

        // зона бесплатной доставки
        var zone = new ymaps.GeoObjectCollection({}, {
            fillColor: 'rgba(217,119,87,.10)',
            strokeColor: '#d97757',
            strokeOpacity: .85,
            strokeWidth: 2,
            strokeDashStyle: ['6', '4']
        });
        zone.add(new ymaps.Circle([CITY, 12000]));
        map.geoObjects.add(zone);

        // точка склада/сервиса
        var placemark = new ymaps.Placemark(CITY, {
            balloonContentHeader: '<b>DesSega&amp;</b> — пункт сборки',
            balloonContentBody: 'Нефтеюганск · доставка за 45 минут<br>Пн–Вс 09:00–22:00',
            hintContent: 'DesSega&'
        }, {
            preset: 'islands#redCircleIcon',
            iconColor: '#d97757'
        });
        map.geoObjects.add(placemark);
        placemark.balloon.open();
    });
})();
</script>

<?php include 'includes/footer.php'; ?>
