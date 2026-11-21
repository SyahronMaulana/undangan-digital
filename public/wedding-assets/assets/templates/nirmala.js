/* NIRMALA — sorot proyektor, film strip yang bisa digeser, dan sinematik halus. */
(function () {
    'use strict';

    if (!document.body.classList.contains('tpl-nirmala')) return;

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var finePointer = window.matchMedia('(pointer: fine)').matches;

    /* Sorot lampu proyektor mengikuti kursor. */
    function startBeam() {
        if (reduceMotion || !finePointer) return;

        var beam = document.createElement('div');
        beam.className = 'nir-beam';
        beam.setAttribute('aria-hidden', 'true');
        document.body.appendChild(beam);

        var frame = null;

        document.addEventListener('mousemove', function (event) {
            if (frame) return;
            frame = window.requestAnimationFrame(function () {
                beam.style.setProperty('--nir-x', event.clientX + 'px');
                beam.style.setProperty('--nir-y', event.clientY + 'px');
                beam.style.opacity = '1';
                frame = null;
            });
        }, { passive: true });
    }

    /* Film strip digeser oleh invitation-core.js ([data-drag-scroll]). */

    /* Angka bab muncul dengan jeda lembut saat terlihat. */
    function startChapterCount() {
        if (reduceMotion || !('IntersectionObserver' in window)) return;

        var nodes = document.querySelectorAll('.nir-chapter strong');
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                entry.target.style.transition = 'opacity .8s ease, transform .8s ease';
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.6 });

        nodes.forEach(function (node) {
            node.style.opacity = '0';
            node.style.transform = 'translateY(6px)';
            observer.observe(node);
        });
    }

    window.setTimeout(function () {
        startBeam();
        startChapterCount();
    }, 700);
})();
