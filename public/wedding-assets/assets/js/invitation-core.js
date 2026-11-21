/* =====================================================================
   Markas Punokawan Wedding — Interaksi undangan
   Cover, musik, hitung mundur, reveal, dock, lightbox, salin, bagikan,
   dan slider galeri. Aman untuk pratinjau (tanpa submit).
   ===================================================================== */
(function () {
    'use strict';

    var body = document.body;
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function $(selector, scope) {
        return (scope || document).querySelector(selector);
    }

    function $all(selector, scope) {
        return Array.prototype.slice.call((scope || document).querySelectorAll(selector));
    }

    function safePlay(audio) {
        if (!audio) return;
        var attempt = audio.play();
        if (attempt && typeof attempt.catch === 'function') {
            attempt.catch(function () { /* autoplay ditolak browser, abaikan */ });
        }
    }

    /* ---------------- Musik latar ---------------- */
    var audio = $('#tw-music');
    var musicButtons = $all('[data-music-toggle]');

    function syncMusicButtons() {
        var playing = audio && !audio.paused;
        musicButtons.forEach(function (button) {
            button.classList.toggle('is-playing', !!playing);
            button.setAttribute('aria-pressed', playing ? 'true' : 'false');
        });
    }

    musicButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            if (!audio) return;
            if (audio.paused) {
                safePlay(audio);
            } else {
                audio.pause();
            }
            setTimeout(syncMusicButtons, 60);
        });
    });

    if (audio) {
        audio.addEventListener('play', syncMusicButtons);
        audio.addEventListener('pause', syncMusicButtons);
        // Berkas gagal dimuat → sembunyikan kontrol agar tidak menyesatkan.
        audio.addEventListener('error', function () {
            musicButtons.forEach(function (button) {
                button.classList.remove('is-playing');
                button.setAttribute('aria-pressed', 'false');
                button.setAttribute('hidden', 'hidden');
            });
        });
    }

    /* ---------------- Cover / buka undangan ---------------- */
    var cover = $('#tw-cover');
    var openers = $all('[data-cover-open]');

    function scrollToHash() {
        var id = (window.location.hash || '').replace('#', '');
        if (!id) return false;
        var target = document.getElementById(id);
        if (!target) return false;
        window.setTimeout(function () {
            target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
        }, reduceMotion ? 30 : 480);
        return true;
    }

    function openInvitation(options) {
        if (!body.classList.contains('cover-open')) return;
        body.classList.remove('cover-open');
        body.classList.add('is-open');
        if (cover) {
            cover.setAttribute('aria-hidden', 'true');
            cover.classList.add('is-dismissed');
        }
        if (!options || options.sound !== false) {
            safePlay(audio);
        }
        setTimeout(syncMusicButtons, 120);
        if (!options || options.keepHash !== true) {
            scrollToHash();
        }
    }

    openers.forEach(function (opener) {
        opener.addEventListener('click', function () {
            openInvitation();
        });
    });

    if (window.location.hash && document.getElementById(window.location.hash.slice(1))) {
        openInvitation({ keepHash: true, sound: false });
    }

    // Tombol escape menutup lightbox
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            var box = $('.tpl-lightbox.is-open');
            if (box) box.classList.remove('is-open');
            body.style.overflow = '';
        }
    });

    /* ---------------- Hitung mundur ---------------- */
    $all('[data-countdown]').forEach(function (node) {
        var target = new Date(node.getAttribute('data-countdown')).getTime();
        if (isNaN(target)) {
            node.setAttribute('hidden', 'hidden');
            return;
        }

        var fields = {
            days: $('[data-days]', node),
            hours: $('[data-hours]', node),
            minutes: $('[data-minutes]', node),
            seconds: $('[data-seconds]', node)
        };

        function pad(value) {
            return String(value).padStart(2, '0');
        }

        function tick() {
            var diff = target - Date.now();
            if (diff <= 0) {
                Object.keys(fields).forEach(function (key) {
                    if (fields[key]) fields[key].textContent = '00';
                });
                node.classList.add('is-done');
                return false;
            }
            var days = Math.floor(diff / 86400000);
            var hours = Math.floor((diff % 86400000) / 3600000);
            var minutes = Math.floor((diff % 3600000) / 60000);
            var seconds = Math.floor((diff % 60000) / 1000);
            if (fields.days) fields.days.textContent = pad(days);
            if (fields.hours) fields.hours.textContent = pad(hours);
            if (fields.minutes) fields.minutes.textContent = pad(minutes);
            if (fields.seconds) fields.seconds.textContent = pad(seconds);
            return true;
        }

        if (tick()) {
            var timer = window.setInterval(function () {
                if (!tick()) window.clearInterval(timer);
            }, 1000);
        }
    });

    /* ---------------- Reveal saat scroll ---------------- */
    var revealNodes = $all('[data-reveal]');

    function showAll() {
        revealNodes.forEach(function (node) { node.classList.add('is-visible'); });
    }

    if (reduceMotion || !('IntersectionObserver' in window)) {
        showAll();
    } else {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

        revealNodes.forEach(function (node) { observer.observe(node); });
    }

    /* ---------------- Dock aktif ---------------- */
    var dockLinks = $all('[data-dock-link]');
    var tracked = dockLinks.map(function (link) {
        return document.getElementById((link.getAttribute('href') || '').replace('#', ''));
    }).filter(Boolean);

    if (dockLinks.length && tracked.length && 'IntersectionObserver' in window) {
        var dockObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                dockLinks.forEach(function (link) {
                    link.classList.toggle('is-active', link.getAttribute('href') === '#' + entry.target.id);
                });
            });
        }, { threshold: 0.2, rootMargin: '-30% 0px -55% 0px' });
        tracked.forEach(function (section) { dockObserver.observe(section); });
    }

    /* ---------------- Lightbox galeri ---------------- */
    var lightbox = null;

    function ensureLightbox() {
        if (lightbox) return lightbox;
        lightbox = document.createElement('div');
        lightbox.className = 'tpl-lightbox';
        lightbox.setAttribute('role', 'dialog');
        lightbox.setAttribute('aria-modal', 'true');
        lightbox.innerHTML = '<button class="tpl-lightbox-close" type="button" aria-label="Tutup">' +
            '<svg class="tpl-icon" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg></button>' +
            '<img alt="">';
        document.body.appendChild(lightbox);
        lightbox.addEventListener('click', function (event) {
            if (event.target === lightbox || event.target.closest('.tpl-lightbox-close')) {
                lightbox.classList.remove('is-open');
                body.style.overflow = '';
            }
        });
        return lightbox;
    }

    $all('[data-lightbox]').forEach(function (trigger) {
        trigger.addEventListener('click', function (event) {
            event.preventDefault();
            var source = trigger.getAttribute('data-lightbox') || trigger.getAttribute('href');
            if (!source) return;
            var box = ensureLightbox();
            $('img', box).src = source;
            box.classList.add('is-open');
            body.style.overflow = 'hidden';
        });
    });

    /* ---------------- Salin teks ---------------- */
    function legacyCopy(value, done) {
        var field = document.createElement('textarea');
        field.value = value;
        field.style.position = 'fixed';
        field.style.opacity = '0';
        document.body.appendChild(field);
        field.select();
        try { document.execCommand('copy'); if (done) done(); } catch (error) { /* abaikan */ }
        document.body.removeChild(field);
    }

    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-copy]');
        if (!button) return;
        event.preventDefault();
        var value = button.getAttribute('data-copy');
        var label = button.querySelector('[data-copy-label]') || button;
        var original = label.textContent;
        function done() {
            label.textContent = 'Tersalin';
            window.setTimeout(function () { label.textContent = original; }, 1800);
        }
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(value).then(done).catch(function () { legacyCopy(value, done); });
        } else {
            legacyCopy(value, done);
        }
    });

    /* ---------------- Bagikan undangan ---------------- */
    $all('[data-share]').forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            var url = button.getAttribute('data-share') || window.location.href;
            var title = button.getAttribute('data-share-title') || document.title;
            var text = button.getAttribute('data-share-text') || 'Undangan pernikahan';
            if (navigator.share) {
                navigator.share({ title: title, text: text, url: url }).catch(function () { /* dibatalkan */ });
                return;
            }
            window.open('https://wa.me/?text=' + encodeURIComponent(text + ' ' + url), '_blank', 'noopener');
        });
    });

    /* ---------------- Slider horizontal ---------------- */
    // Track kadang bersaudara dengan tombol navigasi (bukan di dalamnya),
    // jadi cari ke atas sampai ada wadah yang memuat [data-slide-track].
    function findSlideTrack(slider) {
        var node = slider;
        while (node && node !== document) {
            var track = $('[data-slide-track]', node);
            if (track) return track;
            node = node.parentElement;
        }
        return $('[data-slide-track]');
    }

    $all('[data-slider]').forEach(function (slider) {
        $all('[data-slide]', slider).forEach(function (button) {
            button.addEventListener('click', function () {
                var track = findSlideTrack(slider);
                if (!track) return;
                var direction = button.getAttribute('data-slide') === 'prev' ? -1 : 1;
                track.scrollBy({
                    left: direction * Math.max(280, track.clientWidth * 0.82),
                    behavior: reduceMotion ? 'auto' : 'smooth'
                });
            });
        });
    });

    /* ---------------- Galeri geser (drag mouse + swipe sentuh) ---------------- */
    function enableDragScroll(el) {
        if (!el || el.getAttribute('data-drag-ready') === '1') return;
        el.setAttribute('data-drag-ready', '1');

        var startX = 0;
        var startLeft = 0;
        var pointerId = null;
        var active = false;
        var dragged = false;
        var justDragged = false;
        var lastX = 0;
        var lastTime = 0;
        var velocity = 0;
        var momentumId = null;
        var classTimer = null;

        function canScroll() {
            return (el.scrollWidth - el.clientWidth) > 2;
        }

        function stopMomentum() {
            if (momentumId) {
                window.cancelAnimationFrame(momentumId);
                momentumId = null;
            }
        }

        function startDragState() {
            el.classList.add('is-dragging');
            // Jaring pengaman: bila requestAnimationFrame dihentikan (tab
            // tidak aktif), kelas drag tetap dilepas agar kursor/snap pulih.
            if (classTimer) window.clearTimeout(classTimer);
            classTimer = window.setTimeout(endDragState, 1500);
        }

        function endDragState() {
            el.classList.remove('is-dragging');
            if (classTimer) {
                window.clearTimeout(classTimer);
                classTimer = null;
            }
        }

        function momentum() {
            if (reduceMotion || Math.abs(velocity) < 0.2) {
                momentumId = null;
                endDragState();
                return;
            }
            el.scrollLeft -= velocity;
            velocity *= 0.94;
            momentumId = window.requestAnimationFrame(momentum);
        }

        el.addEventListener('pointerdown', function (event) {
            if (event.pointerType === 'mouse' && event.button !== 0) return;
            if (!canScroll()) return;
            stopMomentum();
            active = true;
            dragged = false;
            justDragged = false;
            pointerId = event.pointerId;
            startX = event.clientX;
            startLeft = el.scrollLeft;
            lastX = event.clientX;
            lastTime = event.timeStamp || Date.now();
            velocity = 0;
        });

        el.addEventListener('pointermove', function (event) {
            if (!active || event.pointerId !== pointerId) return;
            var delta = event.clientX - startX;

            // Pointer baru di-capture setelah melewati ambang, supaya klik
            // biasa (buka lightbox) tetap sampai ke tombol.
            if (!dragged) {
                if (Math.abs(delta) < 6) return;
                dragged = true;
                startDragState();
                try { el.setPointerCapture(pointerId); } catch (error) { /* abaikan */ }
            }

            if (event.cancelable) event.preventDefault();
            el.scrollLeft = startLeft - delta;

            var now = event.timeStamp || Date.now();
            var dt = now - lastTime;
            if (dt > 0) {
                velocity = (event.clientX - lastX) / dt * 16;
            }
            lastX = event.clientX;
            lastTime = now;
        });

        function finish(event) {
            if (event && typeof event.pointerId === 'number' && pointerId !== null && event.pointerId !== pointerId) return;
            if (!active) return;
            active = false;
            if (pointerId !== null) {
                try { el.releasePointerCapture(pointerId); } catch (error) { /* abaikan */ }
            }
            pointerId = null;

            if (!dragged) return;

            justDragged = true;
            if (!reduceMotion && canScroll() && Math.abs(velocity) > 0.4) {
                momentum();
            } else {
                endDragState();
            }
        }

        el.addEventListener('pointerup', finish);
        el.addEventListener('pointercancel', finish);

        // Telan klik yang mengikuti geser agar lightbox tidak terbuka tak sengaja.
        el.addEventListener('click', function (event) {
            if (justDragged) {
                justDragged = false;
                event.preventDefault();
                event.stopPropagation();
            }
        }, true);

        // Roda horizontal (trackpad) menggeser galeri; roda vertikal dibiarkan
        // menggulir halaman agar scroll halaman tidak terkunci.
        el.addEventListener('wheel', function (event) {
            if (!canScroll()) return;
            if (Math.abs(event.deltaX) <= Math.abs(event.deltaY)) return;
            event.preventDefault();
            stopMomentum();
            el.scrollLeft += event.deltaX;
        }, { passive: false });
    }

    $all('[data-slide-track], [data-drag-scroll]').forEach(enableDragScroll);

    /* ---------------- Tahun berjalan ---------------- */
    $all('[data-year]').forEach(function (node) {
        node.textContent = String(new Date().getFullYear());
    });

    /* ---------------- Tandai sudah dibuka ---------------- */
    window.WeddingInvitation = {
        open: openInvitation,
        copy: legacyCopy
    };
})();
