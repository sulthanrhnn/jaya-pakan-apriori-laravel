document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-alert] .alert-close').forEach(button => {
        button.addEventListener('click', () => button.closest('[data-alert]')?.remove());
    });

    document.querySelectorAll('[data-confirm]').forEach(form => {
        form.addEventListener('submit', event => {
            const message = form.getAttribute('data-confirm') || 'Lanjutkan tindakan ini?';
            if (!window.confirm(message)) event.preventDefault();
        });
    });

    document.querySelectorAll('[data-copy]').forEach(button => {
        button.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(button.getAttribute('data-copy') || '');
                const old = button.textContent;
                button.textContent = 'Tersalin ✓';
                setTimeout(() => button.textContent = old, 1500);
            } catch (_) {
                window.prompt('Salin teks berikut:', button.getAttribute('data-copy') || '');
            }
        });
    });

    const toggle = document.querySelector('[data-sidebar-toggle]');
    const sidebar = document.getElementById('staffSidebar');
    if (toggle && sidebar) toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
});
