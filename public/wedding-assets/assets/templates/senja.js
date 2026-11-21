/* SENJA — parallax pita foto dan galeri geser (drag) ala majalah. */
(function () {
    'use strict';

    if (!document.body.classList.contains('tpl-senja')) return;

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* Parallax pita foto */
    var band = document.querySelector('.sen-band');
    var bandImage = band ? band.querySelector('img') : null;

    if (band && bandImage && !reduceMotion) {
        var ticking = false;

        function parallax() {
            var rect = band.getBoundingClientRect();
            if (rect.bottom > 0 && rect.top < window.innerHeight) {
                var progress = (rect.top + rect.height / 2 - window.innerHeight / 2) / window.innerHeight;
                bandImage.style.transform = 'translate3d(0,' + (progress * -34).toFixed(2) + 'px,0)';
            }
            ticking = false;
        }

        window.addEventListener('scroll', function () {
            if (ticking) return;
            ticking = true;
            window.requestAnimationFrame(parallax);
        }, { passive: true });

        parallax();
    }
})();
