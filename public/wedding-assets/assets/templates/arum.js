/* ARUM — serpihan kertas, kemiringan foto, dan parallax scrapbook. */
(function () {
    'use strict';

    if (!document.body.classList.contains('tpl-arum')) return;

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var finePointer = window.matchMedia('(pointer: fine)').matches;

    /* Serbuk kertas hangat melayang pelan di balik konten. */
    function startFlecks() {
        if (reduceMotion || document.querySelector('.arm-flecks')) return;

        var layer = document.createElement('div');
        layer.className = 'arm-flecks';
        layer.setAttribute('aria-hidden', 'true');

        for (var i = 0; i < 14; i += 1) {
            var fleck = document.createElement('span');
            fleck.style.left = (Math.random() * 100).toFixed(2) + '%';
            fleck.style.top = (Math.random() * 100).toFixed(2) + '%';
            fleck.style.width = (3 + Math.random() * 5).toFixed(1) + 'px';
            fleck.style.height = (2 + Math.random() * 3).toFixed(1) + 'px';
            fleck.style.animationDuration = (10 + Math.random() * 14).toFixed(1) + 's';
            fleck.style.animationDelay = (-Math.random() * 16).toFixed(1) + 's';
            fleck.style.opacity = (0.2 + Math.random() * 0.35).toFixed(2);
            layer.appendChild(fleck);
        }

        document.body.appendChild(layer);
    }

    /* Kemiringan natural untuk setiap polaroid galeri. */
    function scatterGallery() {
        var snaps = document.querySelectorAll('.arm-snap');
        snaps.forEach(function (snap, index) {
            var angle = ((index * 37) % 7) - 3;
            if (angle === 0) angle = 1.2;
            snap.style.setProperty('--arm-angle', angle.toFixed(2) + 'deg');
            snap.style.transform = 'rotate(' + angle.toFixed(2) + 'deg)';
        });
    }

    /* Parallax halus pada foto sampul dan strip hero. */
    function startParallax() {
        if (reduceMotion || !finePointer) return;

        var items = [
            { el: document.querySelector('.arm-cover-photo img'), depth: 10 },
            { el: document.querySelector('.arm-hero-strip img'), depth: 7 }
        ].filter(function (item) { return item.el; });

        if (!items.length) return;

        var frame = null;

        document.addEventListener('mousemove', function (event) {
            if (frame) return;
            frame = window.requestAnimationFrame(function () {
                var x = (event.clientX / window.innerWidth - 0.5);
                var y = (event.clientY / window.innerHeight - 0.5);
                items.forEach(function (item) {
                    item.el.style.transform = 'scale(1.045) translate3d(' + (x * item.depth).toFixed(2) + 'px,' + (y * item.depth).toFixed(2) + 'px,0)';
                });
                frame = null;
            });
        }, { passive: true });
    }

    window.setTimeout(function () {
        startFlecks();
        scatterGallery();
        startParallax();
    }, 600);
})();
