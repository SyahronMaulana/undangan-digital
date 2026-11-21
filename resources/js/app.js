import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// Handler generik untuk tombol salin: <button data-copy="teks">.
// Dipakai di dasbor customer (daftar undangan, broadcast) dan panel admin.
function legacyCopy(value) {
    const field = document.createElement('textarea');
    field.value = value;
    field.setAttribute('readonly', '');
    field.style.position = 'fixed';
    field.style.opacity = '0';
    document.body.appendChild(field);
    field.select();
    try {
        document.execCommand('copy');
    } catch (error) {
        // Diamkan: lingkungan tanpa izin clipboard.
    }
    document.body.removeChild(field);
}

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-copy]');
    if (!button) {
        return;
    }

    const value = button.getAttribute('data-copy');
    if (!value) {
        return;
    }

    const label = button.querySelector('[data-copy-label]');
    const original = label ? label.textContent : null;

    const done = () => {
        if (label) {
            label.textContent = 'Tersalin';
            setTimeout(() => { label.textContent = original; }, 1600);
        } else {
            button.classList.add('ring-2', 'ring-emerald-400');
            setTimeout(() => button.classList.remove('ring-2', 'ring-emerald-400'), 1200);
        }
    };

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(value).then(done).catch(() => {
            legacyCopy(value);
            done();
        });
    } else {
        legacyCopy(value);
        done();
    }
});
