/* PUSPITA — batik bergerak, kilau kawung, dan parallax gunungan. */
(function () {
    'use strict';

    if (!document.body.classList.contains('tpl-puspita')) return;

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var finePointer = window.matchMedia('(pointer: fine)').matches;

    /* Butir emas kecil naik perlahan seperti kembang api. */
    function startSparkles() {
        if (reduceMotion || document.querySelector('.pus-sparkles')) return;

        var layer = document.createElement('div');
        layer.className = 'pus-sparkles';
        layer.setAttribute('aria-hidden', 'true');

        for (var i = 0; i < 18; i += 1) {
            var spark = document.createElement('span');
            spark.style.left = (Math.random() * 100).toFixed(2) + '%';
            spark.style.width = (3 + Math.random() * 4).toFixed(1) + 'px';
            spark.style.height = spark.style.width;
            spark.style.animationDuration = (12 + Math.random() * 14).toFixed(1) + 's';
            spark.style.animationDelay = (-Math.random() * 20).toFixed(1) + 's';
            spark.style.opacity = (0.25 + Math.random() * 0.4).toFixed(2);
            layer.appendChild(spark);
        }

        document.body.appendChild(layer);
    }

    /* Motif batik pada hero bergerak pelan saat halaman digulir. */
    function startBatikDrift() {
        var pattern = document.querySelector('.pus-batik-hero');
        if (!pattern || reduceMotion) return;

        var ticking = false;

        function update() {
            var y = window.scrollY || window.pageYOffset || 0;
            pattern.style.backgroundPosition = '0 ' + (y * 0.18).toFixed(1) + 'px';
            ticking = false;
        }

        window.addEventListener('scroll', function () {
            if (ticking) return;
            ticking = true;
            window.requestAnimationFrame(update);
        }, { passive: true });

        update();
    }

    /* Kemiringan bingkai mempelai saat kursor mendekat. */
    function startFrameTilt() {
        if (reduceMotion || !finePointer) return;

        document.querySelectorAll('.pus-person-frame').forEach(function (frame) {
            frame.addEventListener('pointermove', function (event) {
                var rect = frame.getBoundingClientRect();
                var x = (event.clientX - rect.left) / rect.width - 0.5;
                var y = (event.clientY - rect.top) / rect.height - 0.5;
                frame.style.transform = 'perspective(700px) rotateY(' + (x * 7).toFixed(2) + 'deg) rotateX(' + (-y * 7).toFixed(2) + 'deg)';
            }, { passive: true });

            frame.addEventListener('pointerleave', function () {
                frame.style.transform = '';
            });
        });
    }

    window.setTimeout(function () {
        startSparkles();
        startBatikDrift();
        startFrameTilt();
    }, 700);
})();
