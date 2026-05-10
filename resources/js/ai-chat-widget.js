function escapeHtml(text) {
    return String(text)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function formatMessage(text) {
    return escapeHtml(text)
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/`([^`]+)`/g, '<code class="rounded-lg bg-slate-100 px-1.5 py-0.5 font-mono text-[0.85em] text-slate-800">$1</code>')
        .replaceAll('\n', '<br>');
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function el(tag, className, html) {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (html !== undefined) node.innerHTML = html;
    return node;
}

function makeBubble(role, content) {
    const isUser = role === 'user';
    const wrap = el('div', `flex items-start gap-3 ${isUser ? 'justify-end' : 'justify-start'}`);

    if (!isUser) {
        wrap.appendChild(el(
            'div',
            'mt-1 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl border border-blue-100 bg-blue-50 text-blue-600',
            '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3v18M8 7h8M7 11h10M8 15h8"/></svg>'
        ));
    }

    const bubble = el(
        'div',
        [
            'max-w-[82%] rounded-2xl px-5 py-4 text-sm leading-7 shadow-md',
            isUser
                ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white'
                : 'border border-slate-100 bg-white text-slate-800',
        ].join(' '),
        formatMessage(content)
    );
    wrap.appendChild(bubble);
    return wrap;
}

function autoResizeTextarea(input) {
    input.style.height = '0px';
    input.style.height = `${Math.min(input.scrollHeight, 112)}px`;
}

function internalReply(message) {
    const text = message.toLowerCase();

    if (text.includes('doanh thu')) {
        return '**Doanh thu hôm nay:** 13.025.000 đ (2 đơn).\n\nBạn có muốn chuyển sang trang Thống kê để xem báo cáo dòng tiền chi tiết không?';
    }

    if (text.includes('sắp hết') || text.includes('sap het') || text.includes('tồn kho') || text.includes('ton kho')) {
        return '**Hàng sắp hết:** Nẹp chỉ sồi 22mm còn 31 cuộn, sát ngưỡng tối thiểu 30.\n\nKho vật tư còn lại đang ở trạng thái an toàn.';
    }

    if (text.includes('hợp đồng') || text.includes('hop dong') || text.includes('hd-2026-001')) {
        return '**HD-2026-001** thuộc khách hàng Nội Thất Nam Phương.\n\nTổng tiền 185.000.000 đ, đã đặt cọc 55.000.000 đ, công nợ còn 130.000.000 đ.';
    }

    return null;
}

function initAiChatWidget() {
    const root = document.getElementById('ai-chat-root');
    if (!root) return;

    const fab = document.getElementById('ai-chat-fab');
    const panel = document.getElementById('ai-chat-panel');
    const closeBtn = document.getElementById('ai-chat-close');
    const messagesEl = document.getElementById('ai-chat-messages');
    const form = document.getElementById('ai-chat-form');
    const input = document.getElementById('ai-chat-input');
    const sendBtn = document.getElementById('ai-chat-send');
    const voiceBtn = document.getElementById('ai-chat-voice');
    const toastEl = document.getElementById('ai-chat-toast');
    const quickActions = document.getElementById('ai-chat-quick-actions');
    const openIcon = fab?.querySelector('[data-ai-icon-open]');
    const closeIcon = fab?.querySelector('[data-ai-icon-close]');

    if (!fab || !panel || !closeBtn || !messagesEl || !form || !input || !sendBtn || !voiceBtn || !toastEl) return;

    let history = [];
    let isOpen = false;

    function showToast(message) {
        toastEl.textContent = message;
        toastEl.classList.remove('hidden');
        window.setTimeout(() => toastEl.classList.add('hidden'), 1800);
    }

    function append(role, content, save = true) {
        if (save) history.push({ role, content });
        messagesEl.appendChild(makeBubble(role, content));
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function seedGreeting() {
        if (messagesEl.children.length > 0) return;
        append('assistant', '**Admin Assistant v3.0 (Internal Engine)** - Sẵn sàng!\n\nSếp cần em báo cáo Doanh thu, Tồn kho hay tra cứu Hợp đồng ạ?', false);
    }

    function setOpen(next) {
        isOpen = next;
        panel.classList.toggle('hidden', !next);
        panel.classList.toggle('flex', next);
        openIcon?.classList.toggle('hidden', next);
        closeIcon?.classList.toggle('hidden', !next);
        if (next) {
            seedGreeting();
            window.setTimeout(() => input.focus(), 40);
        }
    }

    async function submitMessage(text) {
        const trimmed = text.trim();
        if (!trimmed) return;

        quickActions?.classList.add('hidden');
        append('user', trimmed);
        input.value = '';
        autoResizeTextarea(input);

        const canned = internalReply(trimmed);
        if (canned) {
            window.setTimeout(() => append('assistant', canned), 260);
            return;
        }

        sendBtn.disabled = true;
        const typing = el('div', 'flex justify-start', '<div class="rounded-2xl bg-white px-5 py-4 text-sm font-bold text-slate-400 shadow-md">Đang trả lời...</div>');
        messagesEl.appendChild(typing);
        messagesEl.scrollTop = messagesEl.scrollHeight;

        try {
            const response = await fetch('/ai/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ message: trimmed, history: history.slice(-10) }),
            });
            const data = await response.json().catch(() => ({}));
            typing.remove();
            append('assistant', response.ok ? (data?.reply || 'AI chưa có phản hồi.') : (data?.error || 'Không kết nối được AI.'));
        } catch {
            typing.remove();
            append('assistant', 'Không kết nối được AI. Bạn thử lại sau nhé.');
        } finally {
            sendBtn.disabled = false;
            input.focus();
        }
    }

    fab.addEventListener('click', () => setOpen(!isOpen));
    closeBtn.addEventListener('click', () => setOpen(false));
    input.addEventListener('input', () => autoResizeTextarea(input));
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            form.requestSubmit();
        }
    });
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        submitMessage(input.value);
    });
    document.querySelectorAll('[data-quick-prompt]').forEach((button) => {
        button.addEventListener('click', () => submitMessage(button.dataset.quickPrompt || button.textContent));
    });
    voiceBtn.addEventListener('click', () => showToast('Voice input sẽ dùng cho phiên bản mobile/PWA.'));
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAiChatWidget);
} else {
    initAiChatWidget();
}
