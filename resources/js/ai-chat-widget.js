function escapeHtml(text) {
    return String(text)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function el(tag, className, html) {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (html !== undefined) node.innerHTML = html;
    return node;
}

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

function makeBubble(role, content) {
    const isUser = role === 'user';
    const wrap = el('div', `flex ${isUser ? 'justify-end' : 'justify-start'}`);
    const bubble = el(
        'div',
        [
            'max-w-[85%] rounded-2xl px-4 py-3 text-sm leading-relaxed shadow-sm border',
            isUser
                ? 'bg-indigo-600 text-white border-indigo-600'
                : 'bg-white text-slate-800 border-slate-200/70',
        ].join(' '),
        escapeHtml(content).replaceAll('\n', '<br>')
    );
    wrap.appendChild(bubble);
    return wrap;
}

function loadHistory() {
    try {
        const raw = localStorage.getItem('ai_chat_history');
        const parsed = raw ? JSON.parse(raw) : [];
        if (!Array.isArray(parsed)) return [];
        return parsed
            .filter((t) => t && (t.role === 'user' || t.role === 'assistant') && typeof t.content === 'string')
            .slice(-12);
    } catch {
        return [];
    }
}

function saveHistory(history) {
    try {
        localStorage.setItem('ai_chat_history', JSON.stringify(history.slice(-12)));
    } catch {
        // ignore
    }
}

function clearHistory() {
    try {
        localStorage.removeItem('ai_chat_history');
    } catch {
        // ignore
    }
}

function appendMessage(messagesEl, history, role, content) {
    history.push({ role, content });
    saveHistory(history);
    messagesEl.appendChild(makeBubble(role, content));
    messagesEl.scrollTop = messagesEl.scrollHeight;
}

function setLoadingState(sendBtn, input, loading) {
    sendBtn.disabled = loading;
    input.disabled = loading;
    sendBtn.classList.toggle('opacity-60', loading);
    sendBtn.classList.toggle('cursor-not-allowed', loading);
}

function initAiChatWidget() {
    const root = document.getElementById('ai-chat-root');
    if (!root) return;

    const fab = document.getElementById('ai-chat-fab');
    const panel = document.getElementById('ai-chat-panel');
    const closeBtn = document.getElementById('ai-chat-close');
    const toggleSizeBtn = document.getElementById('ai-chat-toggle-size');
    const clearBtn = document.getElementById('ai-chat-clear');
    const messagesEl = document.getElementById('ai-chat-messages');
    const hintEl = document.getElementById('ai-chat-hint');
    const form = document.getElementById('ai-chat-form');
    const input = document.getElementById('ai-chat-input');
    const sendBtn = document.getElementById('ai-chat-send');
    const confirmEl = document.getElementById('ai-chat-confirm');
    const confirmCancelBtn = document.getElementById('ai-chat-confirm-cancel');
    const confirmOkBtn = document.getElementById('ai-chat-confirm-ok');
    const toastEl = document.getElementById('ai-chat-toast');

    if (
        !fab ||
        !panel ||
        !closeBtn ||
        !toggleSizeBtn ||
        !clearBtn ||
        !messagesEl ||
        !hintEl ||
        !form ||
        !input ||
        !sendBtn ||
        !confirmEl ||
        !confirmCancelBtn ||
        !confirmOkBtn ||
        !toastEl
    )
        return;

    let isOpen = false;
    let isMax = false;
    let history = loadHistory();

    // Render history
    for (const turn of history) {
        messagesEl.appendChild(makeBubble(turn.role, turn.content));
    }
    messagesEl.scrollTop = messagesEl.scrollHeight;

    function openPanel() {
        isOpen = true;
        panel.classList.remove('hidden');
        setTimeout(() => input.focus(), 50);
    }

    function closePanel() {
        isOpen = false;
        panel.classList.add('hidden');
        isMax = false;
        panel.classList.remove('fixed', 'inset-4', 'w-auto', 'h-auto');
        panel.classList.add('absolute', 'bottom-16', 'right-0', 'w-[360px]', 'h-[480px]');
    }

    function toggleSize() {
        isMax = !isMax;
        if (isMax) {
            panel.classList.remove('absolute', 'bottom-16', 'right-0', 'w-[360px]', 'h-[480px]');
            panel.classList.add('fixed', 'inset-4', 'w-auto', 'h-auto');
        } else {
            panel.classList.remove('fixed', 'inset-4', 'w-auto', 'h-auto');
            panel.classList.add('absolute', 'bottom-16', 'right-0', 'w-[360px]', 'h-[480px]');
        }
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    fab.addEventListener('click', () => {
        if (isOpen) closePanel();
        else openPanel();
    });
    closeBtn.addEventListener('click', closePanel);
    toggleSizeBtn.addEventListener('click', toggleSize);

    let toastTimer = null;
    function showToast(message) {
        const box = toastEl.querySelector('div');
        if (box) box.textContent = message;
        toastEl.classList.remove('hidden');
        if (toastTimer) window.clearTimeout(toastTimer);
        toastTimer = window.setTimeout(() => toastEl.classList.add('hidden'), 1600);
    }

    function openConfirm() {
        confirmEl.classList.remove('hidden');
    }
    function closeConfirm() {
        confirmEl.classList.add('hidden');
    }

    clearBtn.addEventListener('click', openConfirm);
    confirmCancelBtn.addEventListener('click', () => {
        closeConfirm();
        input.focus();
    });
    confirmOkBtn.addEventListener('click', () => {
        history = [];
        clearHistory();
        messagesEl.innerHTML = '';
        messagesEl.appendChild(hintEl);
        messagesEl.scrollTop = messagesEl.scrollHeight;
        closeConfirm();
        showToast('Đã xóa lịch sử chat');
        input.focus();
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;

        appendMessage(messagesEl, history, 'user', text);
        input.value = '';

        const csrf = getCsrfToken();
        setLoadingState(sendBtn, input, true);

        const typing = el(
            'div',
            'flex justify-start',
            '<div class="max-w-[85%] rounded-2xl px-4 py-3 text-sm bg-white text-slate-500 border border-slate-200/70 shadow-sm">Đang trả lời…</div>'
        );
        messagesEl.appendChild(typing);
        messagesEl.scrollTop = messagesEl.scrollHeight;

        try {
            const res = await fetch('/ai/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({
                    message: text,
                    history: history.slice(-10),
                }),
            });

            const data = await res.json().catch(() => ({}));
            typing.remove();

            if (!res.ok) {
                const detailMsg =
                    (typeof data?.details === 'string' && data.details) ||
                    data?.details?.error?.message ||
                    data?.details?.error?.status ||
                    data?.body ||
                    '';
                appendMessage(
                    messagesEl,
                    history,
                    'assistant',
                    data?.error
                        ? `Lỗi: ${data.error}${data?.status ? ` (HTTP ${data.status})` : ''}${detailMsg ? `\n${detailMsg}` : ''}`
                        : 'Lỗi kết nối AI. Vui lòng thử lại.'
                );
                return;
            }

            appendMessage(messagesEl, history, 'assistant', data?.reply ?? 'AI không trả lời được.');
        } catch {
            typing.remove();
            appendMessage(messagesEl, history, 'assistant', 'Lỗi kết nối AI. Vui lòng thử lại.');
        } finally {
            setLoadingState(sendBtn, input, false);
            input.focus();
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAiChatWidget);
} else {
    initAiChatWidget();
}
