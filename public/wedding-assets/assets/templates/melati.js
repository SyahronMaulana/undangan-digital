/* MELATI — animasi kelopak melati dan parallax cover. */
(function () {
    'use strict';

    if (!document.body.classList.contains('tpl-melati')) return;

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function startPetals() {
        if (reduceMotion || document.querySelector('.mel-petals')) return;

        var layer = document.createElement('div');
        layer.className = 'mel-petals';
        layer.setAttribute('aria-hidden', 'true');

        for (var i = 0; i < 16; i += 1) {
            var petal = document.createElement('span');
            petal.className = 'mel-petal';
            petal.style.left = (Math.random() * 100).toFixed(2) + '%';
            petal.style.width = (6 + Math.random() * 7).toFixed(1) + 'px';
            petal.style.height = (9 + Math.random() * 9).toFixed(1) + 'px';
            petal.style.animationDuration = (11 + Math.random() * 12).toFixed(1) + 's';
            petal.style.animationDelay = (-Math.random() * 18).toFixed(1) + 's';
            petal.style.setProperty('--mel-drift', (Math.random() * 90 - 45).toFixed(0) + 'px');
            petal.style.setProperty('--mel-drift-end', (Math.random() * 120 - 60).toFixed(0) + 'px');
            petal.style.opacity = (0.35 + Math.random() * 0.45).toFixed(2);
            layer.appendChild(petal);
        }

        document.body.appendChild(layer);
    }

    window.setTimeout(startPetals, 900);

    var coverImage = document.querySelector('.mel-cover-media img');
    var canHover = window.matchMedia('(pointer: fine)').matches;

    if (coverImage && !reduceMotion && canHover) {
        var frame = null;

        document.addEventListener('mousemove', function (event) {
            if (frame) return;
            frame = window.requestAnimationFrame(function () {
                var x = (event.clientX / window.innerWidth - 0.5) * 14;
                var y = (event.clientY / window.innerHeight - 0.5) * 14;
                coverImage.style.transform = 'scale(1.06) translate3d(' + x.toFixed(2) + 'px,' + y.toFixed(2) + 'px,0)';
                frame = null;
            });
        }, { passive: true });
    }
})();
