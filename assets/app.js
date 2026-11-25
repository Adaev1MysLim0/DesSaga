// DesSega& — мягкая интерактивность без «нейро»-эффектов
(function () {
    'use strict';

    // ---------- Count-up для чисел в статистике ----------
    function animateCount(el, target, duration) {
        var startTime = null;
        function frame(ts) {
            if (!startTime) startTime = ts;
            var progress = Math.min((ts - startTime) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);   // ease-out cubic
            el.textContent = Math.round(eased * target);
            if (progress < 1) requestAnimationFrame(frame);
            else el.textContent = target;
        }
        requestAnimationFrame(frame);
    }

    var counters = document.querySelectorAll('.count');
    if (counters.length) {
        if ('IntersectionObserver' in window) {
            // числа оживают, когда блок со счётчиками попадает в поле зрения
            var cio = new IntersectionObserver(function (entries) {
                entries.forEach(function (en) {
                    if (!en.isIntersecting) return;
                    var el = en.target;
                    var target = parseInt(el.getAttribute('data-count') || '0', 10);
                    if (target > 0) animateCount(el, target, 1200);
                    else el.textContent = '0';
                    cio.unobserve(el);
                });
            }, { threshold: .4 });
            counters.forEach(function (el) { cio.observe(el); });
        } else {
            counters.forEach(function (el) {
                var target = parseInt(el.getAttribute('data-count') || '0', 10);
                el.textContent = target > 0 ? target : '0';
            });
        }
    }

    // ---------- Появление блоков при скролле ----------
    var revealEls = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window && revealEls.length) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) {
                    en.target.classList.add('visible');
                    io.unobserve(en.target);
                }
            });
        }, { threshold: .12, rootMargin: '0px 0px -30px 0px' });
        revealEls.forEach(function (el) { io.observe(el); });
    } else {
        revealEls.forEach(function (el) { el.classList.add('visible'); });
    }

    // ---------- Каталог: карточки появляются лесенкой ----------
    var grid = document.querySelector('.grid');
    if (grid && 'IntersectionObserver' in window) {
        var cards = grid.querySelectorAll('.product');
        var gio = new IntersectionObserver(function (entries) {
            var order = 0;
            entries.forEach(function (en) {
                if (!en.isIntersecting) return;
                en.target.style.animationDelay = (order * .04) + 's';
                en.target.classList.add('visible');
                order++;
                gio.unobserve(en.target);
            });
        }, { threshold: .08, rootMargin: '0px 0px -20px 0px' });
        cards.forEach(function (el) {
            el.classList.add('reveal');
            gio.observe(el);
        });
    }

    // ---------- Тост ----------
    var toastEl = null, toastTimer = null;
    function toast(msg) {
        if (!toastEl) {
            toastEl = document.createElement('div');
            toastEl.className = 'toast';
            document.body.appendChild(toastEl);
        }
        toastEl.textContent = msg;
        requestAnimationFrame(function () { toastEl.classList.add('show'); });
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { toastEl.classList.remove('show'); }, 2600);
    }
    window.showToast = toast;

    // ---------- Заглушки (кнопки с data-stub) ----------
    document.querySelectorAll('[data-stub]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (el.tagName === 'A') e.preventDefault();
            toast(el.getAttribute('data-stub') || 'Демо-режим: функция-заглушка');
        });
    });

    // ---------- Чек-лист требований пароля ----------
    var pass = document.getElementById('regPass');
    var hint = document.getElementById('passHint');
    if (pass && hint) {
        var checks = {
            length:  /.{6,}/,
            upper:   /[A-Z]/,
            lower:   /[a-z]/,
            digit:   /[0-9]/,
            special: /[^A-Za-z0-9]/
        };
        var reqs = hint.querySelectorAll('.req');
        function refresh() {
            var v = pass.value;
            reqs.forEach(function (el) {
                var c = el.getAttribute('data-check');
                el.classList.toggle('ok', !!(checks[c] && checks[c].test(v)));
            });
        }
        pass.addEventListener('input', refresh);
        refresh();
        var regForm = document.getElementById('regForm');
        if (regForm) regForm.addEventListener('submit', function (e) {
            var ok = true;
            reqs.forEach(function (el) {
                var c = el.getAttribute('data-check');
                if (!checks[c].test(pass.value)) ok = false;
            });
            if (!ok) {
                e.preventDefault();
                toast('Пароль не соответствует всем требованиям');
            }
        });
    }

    // ---------- Переключатель «E-mail / Телефон» на регистрации ----------
    var segEmail = document.querySelector('.seg [data-method=email]');
    var segPhone = document.querySelector('.seg [data-method=phone]');
    if (segEmail && segPhone) {
        var emailFields = document.getElementById('emailFields');
        var phoneFields = document.getElementById('phoneFields');
        var methodInput = document.getElementById('regMethod');
        function setMethod(m) {
            segEmail.classList.toggle('on', m === 'email');
            segPhone.classList.toggle('on', m === 'phone');
            emailFields.style.display = m === 'email' ? '' : 'none';
            phoneFields.style.display = m === 'phone' ? '' : 'none';
            if (methodInput) methodInput.value = m;
            // отключаем обязательность скрытых полей
            [emailFields, phoneFields].forEach(function (box) {
                var hide = box !== (m === 'email' ? emailFields : phoneFields);
                box.querySelectorAll('input').forEach(function (inp) {
                    if (hide) { inp.dataset.req = inp.required ? '1' : ''; inp.required = false; }
                    else if (inp.dataset.req) { inp.required = true; }
                });
            });
        }
        segEmail.addEventListener('click', function () { setMethod('email'); });
        segPhone.addEventListener('click', function () { setMethod('phone'); });
    }

    // ---------- Простая маска телефона ----------
    document.querySelectorAll('input[type=tel]').forEach(function (inp) {
        inp.addEventListener('input', function () {
            var d = inp.value.replace(/\D/g, '');
            if (d.startsWith('8')) d = '7' + d.slice(1);
            if (d && !d.startsWith('7')) d = '7' + d;
            var out = '+7';
            if (d.length > 1) out += ' (' + d.slice(1, 4);
            if (d.length >= 5) out += ') ' + d.slice(4, 7);
            if (d.length >= 8) out += '-' + d.slice(7, 9);
            if (d.length >= 10) out += '-' + d.slice(9, 11);
            inp.value = d ? out : '';
        });
    });

    // ---------- Превью аватара до загрузки ----------
    var avatarInput = document.getElementById('avatarFile');
    var avatarPrev = document.getElementById('avatarPrev');
    if (avatarInput && avatarPrev) {
        avatarInput.addEventListener('change', function () {
            var f = avatarInput.files && avatarInput.files[0];
            if (!f) return;
            avatarPrev.innerHTML = '<img src="' + URL.createObjectURL(f) + '" alt="">';
        });
    }

    // ---------- Раскрытие карточек заказов ----------
    document.querySelectorAll('.order-card .head').forEach(function (h) {
        h.addEventListener('click', function () {
            h.closest('.order-card').classList.toggle('open');
        });
    });

    // ---------- Подсветка выбранных radio-опций ----------
    document.querySelectorAll('.opt').forEach(function (opt) {
        var inp = opt.querySelector('input');
        if (!inp) return;
        function sync() {
            var group = opt.closest('form') || document;
            group.querySelectorAll('.opt').forEach(function (o) {
                var i = o.querySelector('input');
                o.classList.toggle('sel', i && i.name === inp.name && i.checked);
            });
        }
        inp.addEventListener('change', sync);
        if (inp.checked) sync();
    });

    // ---------- Вкладки профиля: черта-индикатор ----------
    var tabs = document.querySelector('.tabs');
    if (tabs) {
        var line = tabs.querySelector('.tab-line');
        var items = tabs.querySelectorAll('a');
        var current = tabs.querySelector('a.active');
        if (line && items.length) {
            function moveLine(el) {
                if (!el) return;
                line.style.width = el.offsetWidth + 'px';
                line.style.transform = 'translateX(' + el.offsetLeft + 'px)';
                line.style.opacity = '1';
            }
            // черта следует за курсором и возвращается к активной вкладке
            items.forEach(function (a) {
                a.addEventListener('mouseenter', function () { moveLine(a); });
                a.addEventListener('mouseleave', function () { moveLine(current); });
            });
            window.addEventListener('resize', function () { moveLine(current); });
            // позиции вкладки могут сдвинуться после подгрузки шрифтов
            window.addEventListener('load', function () { moveLine(current); });
            if (document.fonts && document.fonts.ready) {
                document.fonts.ready.then(function () { moveLine(current); });
            }
            moveLine(current);
        }
    }

    // ---------- Шапка уплотняется при прокрутке ----------
    var header = document.querySelector('.site-header');
    if (header) {
        var tick = false;
        window.addEventListener('scroll', function () {
            if (tick) return;
            tick = true;
            requestAnimationFrame(function () {
                header.classList.toggle('is-scrolled', window.scrollY > 36);
                tick = false;
            });
        }, { passive: true });
    }

    // ---------- Тактильный отклик кнопок количества ----------
    document.querySelectorAll('.qty a').forEach(function (a) {
        a.addEventListener('click', function () {
            a.style.transform = 'scale(.85)';
            setTimeout(function () { a.style.transform = ''; }, 140);
        });
    });

    // ---------- Открыть заказ по якорю #order-N ----------
    if (window.location.hash.length > 1) {
        try {
            var anchor = document.querySelector(window.location.hash);
            if (anchor && anchor.classList.contains('order-card')) {
                anchor.classList.add('open');
                setTimeout(function () { anchor.scrollIntoView({ block: 'center' }); }, 250);
            }
        } catch (err) { /* невалидный селектор — ничего не делаем */ }
    }
})();
