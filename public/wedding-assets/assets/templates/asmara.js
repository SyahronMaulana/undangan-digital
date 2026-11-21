/* ASMARA — hati melayang, parallax lembut, dan kilau potret. */
(function () {
    'use strict';

    if (!document.body.classList.contains('tpl-asmara')) return;

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var finePointer = window.matchMedia('(pointer: fine)').matches;

    /* Hati kecil naik pelan dari bawah layar. */
    function startHearts() {
        if (reduceMotion || document.querySelector('.asm-hearts')) return;

        var layer = document.createElement('div');
        layer.className = 'asm-hearts';
        layer.setAttribute('aria-hidden', 'true');

        for (var i = 0; i < 16; i += 1) {
            var heart = document.createElement('span');
            var size = 6 + Math.random() * 7;
            heart.style.left = (Math.random() * 100).toFixed(2) + '%';
            heart.style.width = size.toFixed(1) + 'px';
            heart.style.height = size.toFixed(1) + 'px';
            heart.style.animationDuration = (14 + Math.random() * 14).toFixed(1) + 's';
            heart.style.animationDelay = (-Math.random() * 22).toFixed(1) + 's';
            heart.style.opacity = (0.25 + Math.random() * 0.35).toFixed(2);
            layer.appendChild(heart);
        }

        document.body.appendChild(layer);
    }

    /* Parallax foto sampul. */
    function startCoverParallax() {
        var image = document.querySelector('.asm-cover-media img');
        if (!image || reduceMotion || !finePointer) return;

        var frame = null;

        document.addEventListener('mousemove', function (event) {
            if (frame) return;
            frame = window.requestAnimationFrame(function () {
                var x = (event.clientX / window.innerWidth - 0.5) * 16;
                var y = (event.clientY / window.innerHeight - 0.5) * 16;
                image.style.transform = 'scale(1.06) translate3d(' + x.toFixed(2) + 'px,' + y.toFixed(2) + 'px,0)';
                frame = null;
            });
        }, { passive: true });
    }

    /* Potret mempelai sedikit "hidup" saat disentuh. */
    function startPortraitTilt() {
        if (reduceMotion || !finePointer) return;

        document.querySelectorAll('.asm-portrait').forEach(function (portrait) {
            portrait.addEventListener('pointermove', function (event) {
                var rect = portrait.getBoundingClientRect();
                var x = (event.clientX - rect.left) / rect.width - 0.5;
                var y = (event.clientY - rect.top) / rect.height - 0.5;
                portrait.style.transform = 'perspective(700px) rotateY(' + (x * 8).toFixed(2) + 'deg) rotateX(' + (-y * 8).toFixed(2) + 'deg) translateY(-3px)';
            }, { passive: true });

            portrait.addEventListener('pointerleave', function () {
                portrait.style.transform = '';
            });
        });
    }

    window.setTimeout(function () {
        startHearts();
        startCoverParallax();
        startPortraitTilt();
    }, 800);
})();
