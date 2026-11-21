/* MAHARAJA — kilau emas mengikuti kursor dan tilt pada lambang. */
(function () {
    'use strict';

    if (!document.body.classList.contains('tpl-maharaja')) return;

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var finePointer = window.matchMedia('(pointer: fine)').matches;

    /* Kilau emas lembut yang mengikuti kursor di seluruh halaman. */
    function startGlow() {
        if (reduceMotion || !finePointer) return;

        var glow = document.createElement('div');
        glow.className = 'mhr-glow';
        glow.setAttribute('aria-hidden', 'true');
        document.body.appendChild(glow);

        var frame = null;
        var idle = null;

        document.addEventListener('mousemove', function (event) {
            if (frame) return;
            frame = window.requestAnimationFrame(function () {
                glow.style.left = event.clientX + 'px';
                glow.style.top = event.clientY + 'px';
                glow.style.opacity = '1';
                frame = null;

                window.clearTimeout(idle);
                idle = window.setTimeout(function () {
                    glow.style.opacity = '0';
                }, 1600);
            });
        }, { passive: true });
    }

    /* Lambang pada sampul sedikit mengikuti gerak kursor. */
    function startCrestTilt() {
        var crest = document.querySelector('.mhr-crest');
        if (!crest || reduceMotion || !finePointer) return;

        var frame = null;

        document.addEventListener('mousemove', function (event) {
            if (frame) return;
            frame = window.requestAnimationFrame(function () {
                var x = (event.clientX / window.innerWidth - 0.5) * 16;
                var y = (event.clientY / window.innerHeight - 0.5) * 12;
                crest.style.transform = 'perspective(600px) rotateX(' + (-y).toFixed(2) + 'deg) rotateY(' + x.toFixed(2) + 'deg)';
                frame = null;
            });
        }, { passive: true });
    }

    /* Kilau berjalan pada bingkai emas ketika kartu disentuh. */
    function startCardSheen() {
        if (reduceMotion || !finePointer) return;

        document.querySelectorAll('.mhr-event, .mhr-gift').forEach(function (card) {
            card.addEventListener('pointermove', function (event) {
                var rect = card.getBoundingClientRect();
                var x = ((event.clientX - rect.left) / rect.width) * 100;
                var y = ((event.clientY - rect.top) / rect.height) * 100;
                card.style.setProperty('--mhr-x', x.toFixed(1) + '%');
                card.style.setProperty('--mhr-y', y.toFixed(1) + '%');
            }, { passive: true });
        });
    }

    window.setTimeout(function () {
        startGlow();
        startCrestTilt();
        startCardSheen();
    }, 400);
})();
