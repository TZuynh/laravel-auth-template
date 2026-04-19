function initUsersDeletePopup() {
    const modal = document.getElementById('users-delete-modal');
    if (!modal) return;

    const overlay = document.getElementById('users-delete-overlay');
    const cancelBtn = document.getElementById('users-delete-cancel');
    const confirmBtn = document.getElementById('users-delete-confirm');
    const nameEl = document.getElementById('users-delete-name');
    const form = document.getElementById('users-delete-form');

    if (!overlay || !cancelBtn || !confirmBtn || !nameEl || !form) return;

    let currentAction = '';

    function open(action, userName) {
        currentAction = action;
        form.setAttribute('action', action);
        nameEl.textContent = userName || 'người dùng này';
        modal.classList.remove('hidden');
    }

    function close() {
        modal.classList.add('hidden');
        currentAction = '';
    }

    document.addEventListener('click', (e) => {
        const btn = e.target && e.target.closest ? e.target.closest('[data-users-delete]') : null;
        if (!btn) return;
        e.preventDefault();
        const action = btn.getAttribute('data-action') || '';
        const userName = btn.getAttribute('data-user-name') || '';
        if (!action) return;
        open(action, userName);
    });

    overlay.addEventListener('click', close);
    cancelBtn.addEventListener('click', close);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') close();
    });

    confirmBtn.addEventListener('click', () => {
        if (!currentAction) return;
        form.submit();
    });
}

function initUsersSearch() {
    const form = document.querySelector('[data-users-search]');
    if (!form) return;
    const input = form.querySelector('input[name="q"]');
    if (!input) return;

    let timer = null;
    let lastValue = input.value;

    input.addEventListener('input', () => {
        const value = input.value;
        if (timer) window.clearTimeout(timer);
        timer = window.setTimeout(() => {
            if (value === lastValue) return;
            lastValue = value;
            form.requestSubmit ? form.requestSubmit() : form.submit();
        }, 350);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initUsersDeletePopup();
        initUsersSearch();
    });
} else {
    initUsersDeletePopup();
    initUsersSearch();
}
