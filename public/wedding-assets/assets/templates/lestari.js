/* LESTARI — spora taman yang melayang dan parallax lengkung cover. */
(function () {
    'use strict';

    if (!document.body.classList.contains('tpl-lestari')) return;

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function startSpores() {
        if (reduceMotion || document.querySelector('.les-spores')) return;

        var layer = document.createElement('div');
        layer.className = 'les-spores';
        layer.setAttribute('aria-hidden', 'true');

        for (var i = 0; i < 12; i += 1) {
            var spore = document.createElement('span');
            spore.className = 'les-spore';
            spore.style.left = (Math.random() * 100).toFixed(2) + '%';
            spore.style.bottom = (-10 + Math.random() * 40).toFixed(0) + '%';
            spore.style.animationDuration = (16 + Math.random() * 16).toFixed(1) + 's';
            spore.style.animationDelay = (-Math.random() * 24).toFixed(1) + 's';
            spore.style.opacity = (0.3 + Math.random() * 0.5).toFixed(2);
            layer.appendChild(spore);
        }

        document.body.appendChild(layer);
    }

    window.setTimeout(startSpores, 1300);

    var arch = document.querySelector('.les-cover-arch img');

    if (arch && !reduceMotion && window.matchMedia('(pointer: fine)').matches) {
        var ticking = false;

        document.addEventListener('mousemove', function (event) {
            if (ticking) return;
            ticking = true;
            window.requestAnimationFrame(function () {
                var x = (event.clientX / window.innerWidth - 0.5) * 10;
                var y = (event.clientY / window.innerHeight - 0.5) * 10;
                arch.style.transform = 'scale(1.03) translate3d(' + x.toFixed(2) + 'px,' + y.toFixed(2) + 'px,0)';
                ticking = false;
            });
        }, { passive: true });
    }
})();
