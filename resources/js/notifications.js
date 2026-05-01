function escapeHtml(text) {
    return String(text)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

function formatTime(timestamp) {
    if (!timestamp) return '';

    const diff = Math.max(0, Date.now() - Number(timestamp));
    const minutes = Math.floor(diff / 60000);
    const isVi = (document.documentElement.lang || 'vi').toLowerCase().startsWith('vi');

    if (minutes < 1) return isVi ? 'Vừa xong' : 'Just now';
    if (minutes < 60) return isVi ? `${minutes} phút trước` : `${minutes} minute${minutes > 1 ? 's' : ''} ago`;

    const hours = Math.floor(minutes / 60);
    if (hours < 24) return isVi ? `${hours} giờ trước` : `${hours} hour${hours > 1 ? 's' : ''} ago`;

    const days = Math.floor(hours / 24);
    return isVi ? `${days} ngày trước` : `${days} day${days > 1 ? 's' : ''} ago`;
}

function createNotificationItem(notification, removeNotification) {
    const item = document.createElement('div');
    item.className = 'group rounded-[1.5rem] border border-slate-200 bg-slate-50/80 p-4 transition-colors hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-950/70 dark:hover:bg-slate-900';

    const colorClasses = notification.type === 'success'
        ? 'bg-emerald-500 text-white'
        : 'bg-rose-500 text-white';

    item.innerHTML = `
        <div class="flex items-start gap-3">
            <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full ${colorClasses}">
                ${notification.type === 'success' ? '&#10003;' : '!'}
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold leading-6 text-slate-900 dark:text-slate-100">${escapeHtml(notification.message)}</p>
                <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">${formatTime(notification.timestamp)}</p>
            </div>
            <button type="button" class="rounded-xl p-1 text-slate-400 opacity-0 transition-all group-hover:opacity-100 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10 dark:hover:text-rose-300" data-notification-remove>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    `;

    item.querySelector('[data-notification-remove]')?.addEventListener('click', () => removeNotification(notification.id));

    return item;
}

export function initNotifications() {
    const toggle = document.getElementById('notification-toggle');
    const dropdown = document.getElementById('notification-dropdown');
    const badge = document.getElementById('notification-badge');
    const list = document.getElementById('notification-list');
    const clearAll = document.getElementById('notification-clear-all');

    if (!toggle || !dropdown || !badge || !list || !clearAll) return;

    let notifications = [];
    try {
        notifications = JSON.parse(dropdown.dataset.notifications || '[]');
        if (!Array.isArray(notifications)) notifications = [];
    } catch {
        notifications = [];
    }

    let isOpen = false;

    function positionDropdown() {
        const rect = toggle.getBoundingClientRect();
        const viewportPadding = 16;
        const dropdownWidth = Math.min(380, window.innerWidth - viewportPadding * 2);
        const left = Math.min(
            Math.max(viewportPadding, rect.right - dropdownWidth),
            window.innerWidth - dropdownWidth - viewportPadding
        );

        dropdown.style.width = `${dropdownWidth}px`;
        dropdown.style.left = `${left}px`;
        dropdown.style.right = 'auto';
        dropdown.style.top = `${rect.bottom + 12}px`;
    }

    function render() {
        list.innerHTML = '';

        if (notifications.length === 0) {
            list.innerHTML = `
                <div class="rounded-2xl border border-dashed border-slate-200 p-4 text-center text-sm text-slate-400 dark:border-slate-800 dark:text-slate-500">
                    ${dropdown.dataset.emptyText || 'No notifications yet.'}
                </div>
            `;
            badge.classList.add('hidden');
            return;
        }

        badge.classList.remove('hidden');
        notifications.forEach((notification) => {
            list.appendChild(createNotificationItem(notification, removeNotification));
        });
    }

    async function sendDelete(url) {
        const response = await fetch(url, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
        });

        if (!response.ok) {
            throw new Error('Request failed');
        }
    }

    async function removeNotification(id) {
        const template = dropdown.dataset.removeUrlTemplate || '';
        if (!template) return;

        const url = template.replace('__ID__', String(id));

        try {
            await sendDelete(url);
            notifications = notifications.filter((notification) => String(notification.id) !== String(id));
            render();
        } catch {
            // ignore
        }
    }

    async function clearNotifications() {
        const url = dropdown.dataset.clearUrl;
        if (!url) return;

        try {
            await sendDelete(url);
            notifications = [];
            render();
        } catch {
            // ignore
        }
    }

    function openDropdown() {
        positionDropdown();
        dropdown.classList.remove('hidden');
        toggle.setAttribute('aria-expanded', 'true');
        isOpen = true;
    }

    function closeDropdown() {
        dropdown.classList.add('hidden');
        toggle.setAttribute('aria-expanded', 'false');
        isOpen = false;
    }

    toggle.addEventListener('click', (event) => {
        event.stopPropagation();
        if (isOpen) {
            closeDropdown();
        } else {
            openDropdown();
        }
    });

    clearAll.addEventListener('click', clearNotifications);

    document.addEventListener('click', (event) => {
        if (!dropdown.contains(event.target) && !toggle.contains(event.target)) {
            closeDropdown();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeDropdown();
        }
    });

    window.addEventListener('resize', () => {
        if (isOpen) positionDropdown();
    });

    window.addEventListener('scroll', () => {
        if (isOpen) positionDropdown();
    }, true);

    render();
}

document.addEventListener('DOMContentLoaded', initNotifications);
