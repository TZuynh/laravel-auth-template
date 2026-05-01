function escapeHtml(text) {
    return String(text)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function formatMessage(text) {
    const escaped = escapeHtml(text);

    return escaped
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/`([^`]+)`/g, '<code class="rounded-lg bg-slate-100 px-1.5 py-0.5 font-mono text-[0.85em] text-slate-800 dark:bg-slate-800 dark:text-slate-100">$1</code>')
        .replaceAll('\n', '<br>');
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
            'max-w-[88%] rounded-[1.7rem] border px-4 py-3 text-sm leading-6 shadow-sm whitespace-pre-wrap',
            isUser
                ? 'border-blue-600 bg-gradient-to-br from-sky-500 via-blue-600 to-slate-900 text-white'
                : 'border-slate-200 bg-white/90 text-slate-800 dark:border-slate-800 dark:bg-slate-900/90 dark:text-slate-100',
        ].join(' '),
        formatMessage(content)
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
            .filter((turn) => turn && (turn.role === 'user' || turn.role === 'assistant') && typeof turn.content === 'string')
            .slice(-16);
    } catch {
        return [];
    }
}

function saveHistory(history) {
    try {
        localStorage.setItem('ai_chat_history', JSON.stringify(history.slice(-16)));
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

function autoResizeTextarea(input) {
    input.style.height = '0px';
    input.style.height = `${Math.min(input.scrollHeight, 128)}px`;
}

function renderHint(messagesEl) {
    const hint = el(
        'div',
        'rounded-[1.6rem] border border-dashed border-slate-200 bg-white/65 px-4 py-4 text-sm leading-6 text-slate-500 dark:border-slate-800 dark:bg-slate-900/40 dark:text-slate-400',
        'Bạn có thể hỏi: <strong>có bao nhiêu user</strong>, <strong>products active/inactive</strong>, <strong>schema bảng users</strong>, <strong>export/import CSV</strong>.'
    );
    hint.id = 'ai-chat-empty-state';
    messagesEl.appendChild(hint);
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
    const form = document.getElementById('ai-chat-form');
    const input = document.getElementById('ai-chat-input');
    const sendBtn = document.getElementById('ai-chat-send');
    const confirmEl = document.getElementById('ai-chat-confirm');
    const confirmCancelBtn = document.getElementById('ai-chat-confirm-cancel');
    const confirmOkBtn = document.getElementById('ai-chat-confirm-ok');
    const toastEl = document.getElementById('ai-chat-toast');

    if (!fab || !panel || !closeBtn || !toggleSizeBtn || !clearBtn || !messagesEl || !form || !input || !sendBtn || !confirmEl || !confirmCancelBtn || !confirmOkBtn || !toastEl) {
        return;
    }

    let isOpen = false;
    let isMax = false;
    let history = loadHistory();

    if (history.length === 0) {
        renderHint(messagesEl);
    } else {
        for (const turn of history) {
            messagesEl.appendChild(makeBubble(turn.role, turn.content));
        }
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function openPanel() {
        isOpen = true;
        panel.classList.remove('hidden');
        panel.classList.add('flex');
        setTimeout(() => {
            autoResizeTextarea(input);
            input.focus();
        }, 50);
    }

    function closePanel() {
        isOpen = false;
        panel.classList.add('hidden');
        panel.classList.remove('flex');
        isMax = false;
        panel.classList.remove('fixed', 'inset-4', 'h-auto', 'w-auto', 'max-w-none');
        panel.classList.add('absolute', 'bottom-20', 'right-0', 'h-[min(78vh,720px)]', 'w-[min(92vw,430px)]');
    }

    function toggleSize() {
        isMax = !isMax;

        if (isMax) {
            panel.classList.remove('absolute', 'bottom-20', 'right-0', 'h-[min(78vh,720px)]', 'w-[min(92vw,430px)]');
            panel.classList.add('fixed', 'inset-4', 'h-auto', 'w-auto', 'max-w-none');
        } else {
            panel.classList.remove('fixed', 'inset-4', 'h-auto', 'w-auto', 'max-w-none');
            panel.classList.add('absolute', 'bottom-20', 'right-0', 'h-[min(78vh,720px)]', 'w-[min(92vw,430px)]');
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

        if (toastTimer) {
            window.clearTimeout(toastTimer);
        }

        toastTimer = window.setTimeout(() => toastEl.classList.add('hidden'), 1800);
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
        renderHint(messagesEl);
        messagesEl.scrollTop = messagesEl.scrollHeight;
        closeConfirm();
        showToast('Đã xóa lịch sử chat');
        input.focus();
    });

    input.addEventListener('input', () => autoResizeTextarea(input));

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            form.requestSubmit();
        }
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const text = input.value.trim();
        if (!text) return;

        document.getElementById('ai-chat-empty-state')?.remove();
        appendMessage(messagesEl, history, 'user', text);
        input.value = '';
        autoResizeTextarea(input);

        const csrf = getCsrfToken();
        setLoadingState(sendBtn, input, true);

        const typing = el(
            'div',
            'flex justify-start',
            '<div class="max-w-[85%] rounded-[1.5rem] border border-slate-200 bg-white/90 px-4 py-3 text-sm text-slate-500 shadow-sm dark:border-slate-800 dark:bg-slate-900/90 dark:text-slate-400">Đang trả lời...</div>'
        );

        messagesEl.appendChild(typing);
        messagesEl.scrollTop = messagesEl.scrollHeight;

        try {
            const response = await fetch('/ai/chat', {
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

            const data = await response.json().catch(() => ({}));
            typing.remove();

            if (!response.ok) {
                const detailMessage =
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
                        ? `Lỗi: ${data.error}${data?.status ? ` (HTTP ${data.status})` : ''}${detailMessage ? `\n${detailMessage}` : ''}`
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
            autoResizeTextarea(input);
            input.focus();
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAiChatWidget);
} else {
    initAiChatWidget();
}
