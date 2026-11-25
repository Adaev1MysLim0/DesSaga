<?php
// AI-поддержка DesSega&: DeepSeek с локальным резервным ботом.
// AJAX-обработка ДО вывода HTML, иначе ответ не будет валидным JSON.
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/ai.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['ajax'] ?? '') === '1') {
    header('Content-Type: application/json; charset=utf-8');

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Для авторизованных имя берём из сессии
    if (isAuth()) {
        $name = trim($_SESSION['user_name'] ?? '') ?: $name;
    }

    if ($name === '' || $message === '' || mb_strlen($message) > 4000) {
        echo json_encode(['ok' => false, 'reply' => 'Заполните имя и текст обращения.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // История диалога из браузера (для контекста DeepSeek)
    $history = [];
    if (!empty($_POST['history'])) {
        $raw = json_decode($_POST['history'], true);
        if (is_array($raw)) {
            foreach ($raw as $m) {
                $role = ($m['role'] ?? '') === 'assistant' ? 'assistant' : 'user';
                $content = trim((string)($m['content'] ?? ''));
                if ($content !== '') $history[] = ['role' => $role, 'content' => mb_substr($content, 0, 1500)];
            }
        }
    }

    $userId = isAuth() ? (int)($_SESSION['user_id'] ?? 0) : null;

    // Ответ AI (DeepSeek или локальные правила)
    $reply = ai_support_reply($message, $history);

    // Сохраняем обращение вместе с ответом бота
    $stmt = $pdo->prepare("INSERT INTO support_messages(user_id, name, email, message, reply, status) VALUES(?,?,?,?,?,?)");
    $stmt->execute([$userId, $name, $email ?: null, $message, $reply, 'pending']);

    echo json_encode([
        'ok'    => true,
        'reply' => $reply,
        'ai'    => ai_available(),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$title = 'Поддержка — DesSega&';
include 'includes/header.php';
?>

<section class="page">
    <div class="wrap">
        <div class="page-head reveal">
            <div class="eyebrow">Поддержка</div>
            <h1>Чем можем помочь?</h1>
            <p class="sub">Напишите Сегочке — нашему AI-помощнику на DeepSeek. Она ответит мгновенно,
               а сложный вопрос передаст живому модератору.</p>
        </div>

        <div class="support-grid" style="display:grid;grid-template-columns:1fr 340px;gap:22px;align-items:start;">
            <!-- AI-чат -->
            <div class="ai-chat reveal">
                <div class="ai-chat-head">
                    <div class="bot-ava"><?= icon('chat', 20) ?></div>
                    <div>
                        <div class="bot-name">Сегочка · помощник DesSega&amp;</div>
                        <div class="bot-status"><span class="dot"></span>онлайн, отвечает мгновенно</div>
                    </div>
                    <span class="bot-tag"><?= ai_available() ? 'DeepSeek AI' : 'AI-режим офлайн' ?></span>
                </div>

                <div class="ai-chat-body" id="chatMessages" aria-live="polite">
                    <div class="chat-msg bot">
                        <div class="chat-ava"><?= icon('chat', 14) ?></div>
                        <div>
                            <div class="chat-bubble">Здравствуйте! Я Сегочка — AI-помощник DesSega&amp;. Спросите про заказ, доставку, оплату или возврат. Если вопрос потребует человека — я передам его модератору.</div>
                            <div class="chat-time">сейчас</div>
                        </div>
                    </div>
                </div>

                <div class="ai-chat-quick" id="chatQuick">
                    <button type="button" class="chip" data-q="Где мой заказ?">Где мой заказ?</button>
                    <button type="button" class="chip" data-q="Как оплатить заказ по СБП?">Оплата по СБП</button>
                    <button type="button" class="chip" data-q="Хочу вернуть деньги за заказ">Возврат денег</button>
                    <button type="button" class="chip" data-q="Как стать курьером?">Стать курьером</button>
                </div>

                <form class="ai-chat-form" id="supportForm">
                    <?php if (!isAuth()): ?>
                    <div class="ai-chat-fields">
                        <input type="text" id="chatName" required placeholder="Ваше имя" autocomplete="name">
                        <input type="email" id="chatEmail" placeholder="E-mail (необязательно)" autocomplete="email">
                    </div>
                    <?php else: ?>
                    <input type="hidden" id="chatName" value="<?= e($_SESSION['user_name'] ?? '') ?>">
                    <input type="hidden" id="chatEmail" value="">
                    <?php endif; ?>
                    <div class="ai-chat-row">
                        <textarea id="chatInput" rows="1" required
                                  placeholder="Опишите ваш вопрос… (Enter — отправить)"></textarea>
                        <button type="submit" class="btn btn-primary" id="chatSend" aria-label="Отправить">
                            <?= icon('send', 17) ?><span>Отправить</span>
                        </button>
                    </div>
                    <div class="ai-note">
                        <?= icon('lock', 13) ?>
                        <?= ai_available()
                            ? 'Ответы генерирует DeepSeek. Обращения сохраняются — модератор их тоже видит.'
                            : 'Без API-ключа DeepSeek бот отвечает по встроенным правилам. Настройте DEEPSEEK_API_KEY в config.php.' ?>
                    </div>
                </form>
            </div>

            <!-- Боковая панель -->
            <div>
                <div class="card-box">
                    <h2 style="font-size:17px;margin-bottom:14px;"><?= icon('clock', 18) ?> Время ответа</h2>
                    <div class="info-rows">
                        <div class="row"><span>AI-помощник</span><span class="v" style="color:var(--green);">мгновенно</span></div>
                        <div class="row"><span>Модераторы</span><span class="v">до 24 часов</span></div>
                        <div class="row"><span>Часы работы</span><span class="v">Пн–Пт 9:00–18:00</span></div>
                    </div>
                </div>

                <div class="card-box">
                    <h2 style="font-size:17px;margin-bottom:14px;"><?= icon('mail', 18) ?> Контакты</h2>
                    <div class="info-rows">
                        <div class="row"><span>Телефон</span><span class="v">8 (3463) 00-00-00</span></div>
                        <div class="row"><span>E-mail</span><span class="v" style="word-break:break-all;">info@dessega.ru</span></div>
                        <div class="row"><span>Telegram</span><span class="v">@dessega_support</span></div>
                    </div>
                </div>

                <div class="card-box" style="background:var(--brand-soft);border-color:var(--brand);">
                    <div style="font-size:14px;color:var(--brand-ink);line-height:1.6;">
                        <strong>Совет:</strong> укажите номер заказа — так Сегочка или модератор
                        помогут быстрее. Номер виден в личном кабинете.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function() {
    'use strict';
    var form       = document.getElementById('supportForm');
    var input      = document.getElementById('chatInput');
    var nameInput  = document.getElementById('chatName');
    var emailInput = document.getElementById('chatEmail');
    var sendBtn    = document.getElementById('chatSend');
    var body       = document.getElementById('chatMessages');
    var quick      = document.getElementById('chatQuick');
    var history    = [];   // диалог для контекста DeepSeek
    var busy       = false;

    function timeNow() {
        var d = new Date();
        return ('0' + d.getHours()).slice(-2) + ':' + ('0' + d.getMinutes()).slice(-2);
    }

    function addMsg(text, isBot) {
        var wrap = document.createElement('div');
        wrap.className = 'chat-msg' + (isBot ? ' bot' : ' me');

        var ava = document.createElement('div');
        ava.className = 'chat-ava';
        ava.textContent = isBot ? 'С' : 'Вы';
        if (isBot) ava.style.fontSize = '11px';

        var box = document.createElement('div');
        var bubble = document.createElement('div');
        bubble.className = 'chat-bubble';
        bubble.textContent = text;
        var time = document.createElement('div');
        time.className = 'chat-time';
        time.textContent = timeNow();
        box.appendChild(bubble);
        box.appendChild(time);

        wrap.appendChild(ava);
        wrap.appendChild(box);
        body.appendChild(wrap);
        body.scrollTop = body.scrollHeight;
    }

    function showTyping() {
        var wrap = document.createElement('div');
        wrap.className = 'chat-msg bot';
        wrap.id = 'typingRow';
        wrap.innerHTML =
            '<div class="chat-ava" style="font-size:11px;">С</div>' +
            '<div class="chat-bubble"><span class="chat-typing"><i></i><i></i><i></i></span></div>';
        body.appendChild(wrap);
        body.scrollTop = body.scrollHeight;
    }
    function hideTyping() {
        var t = document.getElementById('typingRow');
        if (t && t.parentNode) t.parentNode.removeChild(t);
    }

    function send(text) {
        if (busy) return;
        var name = nameInput.value.trim();
        if (!name) {
            nameInput.focus();
            if (nameInput.type !== 'hidden') nameInput.style.borderColor = 'var(--red)';
            return;
        }
        if (!text) { input.focus(); return; }

        busy = true;
        sendBtn.disabled = true;
        addMsg(text, false);
        history.push({ role: 'user', content: text });
        input.value = '';
        autosize();
        showTyping();

        var fd = new FormData();
        fd.append('ajax', '1');
        fd.append('name', name);
        fd.append('email', emailInput.value.trim());
        fd.append('message', text);
        fd.append('history', JSON.stringify(history.slice(-12)));

        fetch('support.php', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                hideTyping();
                var reply = data.reply || 'Не удалось получить ответ. Попробуйте ещё раз или позвоните: 8 (3463) 00-00-00.';
                addMsg(reply, true);
                history.push({ role: 'assistant', content: reply });
            })
            .catch(function() {
                hideTyping();
                var reply = 'Связь потерялась, но обращение записано. Модераторы ответят в течение 24 часов.';
                addMsg(reply, true);
            })
            .finally(function() {
                busy = false;
                sendBtn.disabled = false;
                input.focus();
            });
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        send(input.value.trim());
    });

    // Enter — отправить, Shift+Enter — перенос строки
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            send(input.value.trim());
        }
    });

    // Автовысота textarea
    function autosize() {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 120) + 'px';
    }
    input.addEventListener('input', autosize);

    // Быстрые вопросы
    quick.addEventListener('click', function(e) {
        var chip = e.target.closest('.chip');
        if (chip) send(chip.getAttribute('data-q'));
    });
})();
</script>

<?php include 'includes/footer.php'; ?>
