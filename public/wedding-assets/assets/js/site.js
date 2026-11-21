(function () {
    'use strict';

    const nav = document.querySelector('[data-nav]');
    if (nav) {
        const onScroll = () => nav.classList.toggle('is-scrolled', window.scrollY > 18);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    const toggle = document.querySelector('[data-menu-toggle]');
    const menu = document.querySelector('[data-mobile-menu]');
    if (toggle && menu) {
        toggle.addEventListener('click', () => {
            const open = menu.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    if ('IntersectionObserver' in window) {
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const items = document.querySelectorAll('[data-reveal]');
        if (reduce) {
            items.forEach((el) => el.classList.add('is-visible'));
        } else {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
            items.forEach((el) => observer.observe(el));
        }
    } else {
        document.querySelectorAll('[data-reveal]').forEach((el) => el.classList.add('is-visible'));
    }

    // Preview device switch
    const deviceButtons = document.querySelectorAll('[data-device]');
    const previewFrame = document.querySelector('[data-preview-frame]');
    if (deviceButtons.length && previewFrame) {
        deviceButtons.forEach((button) => {
            button.addEventListener('click', () => {
                deviceButtons.forEach((b) => b.classList.remove('is-active'));
                button.classList.add('is-active');
                const mobile = button.dataset.device === 'mobile';
                previewFrame.classList.toggle('is-desktop', !mobile);
            });
        });
    }

    // Generic copy button: <button data-copy="text">
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-copy]');
        if (!button) return;
        event.preventDefault();
        const value = button.getAttribute('data-copy');
        const done = () => {
            const original = button.dataset.originalLabel || button.textContent;
            button.dataset.originalLabel = original;
            button.textContent = 'Tersalin';
            setTimeout(() => { button.textContent = original; }, 1800);
        };
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(value).then(done).catch(() => fallbackCopy(value, done));
        } else {
            fallbackCopy(value, done);
        }
    });

    function fallbackCopy(value, done) {
        const input = document.createElement('textarea');
        input.value = value;
        input.style.position = 'fixed';
        input.style.opacity = '0';
        document.body.appendChild(input);
        input.select();
        try { document.execCommand('copy'); done(); } catch (e) {}
        document.body.removeChild(input);
    }

    if (window.lucide) {
        window.lucide.createIcons();
    }
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) window.lucide.createIcons();
    });
})();
