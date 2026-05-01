@auth
<div id="ai-chat-root" class="fixed bottom-6 right-6 z-[60]">
    <style>
        @keyframes ai-chat-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        @keyframes ai-chat-glow {
            0%, 100% { box-shadow: 0 24px 70px rgba(14, 23, 56, 0.18); }
            50% { box-shadow: 0 30px 90px rgba(37, 99, 235, 0.24); }
        }

        .ai-chat-fab-glow {
            animation: ai-chat-float 3s ease-in-out infinite, ai-chat-glow 3s ease-in-out infinite;
        }
    </style>

    <button
        id="ai-chat-fab"
        type="button"
        class="group fixed bottom-6 right-6 z-50 inline-flex h-16 w-16 items-center justify-center rounded-[1.6rem] bg-gradient-to-br from-sky-500 via-blue-600 to-slate-900 text-white shadow-2xl transition-all duration-300 hover:scale-105 active:scale-95 ai-chat-fab-glow"
        aria-label="Open AI Chat"
    >
        <svg class="h-8 w-8 transition-transform duration-300 group-hover:rotate-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 6.5A2.5 2.5 0 016.5 4h11A2.5 2.5 0 0120 6.5v7A2.5 2.5 0 0117.5 16H10l-4.5 4v-4H6.5A2.5 2.5 0 014 13.5v-7z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 9h8M8 12h5" />
        </svg>
        <span class="absolute -right-1 -top-1 h-4 w-4 rounded-full border-2 border-white bg-emerald-400"></span>
    </button>

    <section
        id="ai-chat-panel"
        class="absolute bottom-20 right-0 hidden h-[min(78vh,720px)] w-[min(92vw,430px)] flex-col overflow-hidden rounded-[2rem] border border-slate-200/80 bg-[radial-gradient(circle_at_top,_rgba(125,211,252,0.18),_rgba(255,255,255,0.98)_36%,_rgba(255,255,255,0.98)_100%)] shadow-[0_28px_90px_rgba(15,23,42,0.18)] backdrop-blur-xl dark:border-slate-800 dark:bg-[radial-gradient(circle_at_top,_rgba(14,165,233,0.18),_rgba(2,6,23,0.96)_36%,_rgba(2,6,23,0.98)_100%)]"
    >
        <header class="shrink-0 border-b border-slate-200/80 px-5 py-4 dark:border-slate-800">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 via-blue-600 to-slate-900 text-white shadow-lg shadow-sky-500/20">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 12h6M9 16h3M8 4h8a3 3 0 013 3v10a3 3 0 01-3 3H8a3 3 0 01-3-3V7a3 3 0 013-3z" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-base font-black tracking-tight text-slate-900 dark:text-slate-100">AI Database Assistant</p>
                            <p class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">Chỉ trả lời về bảng, cột, schema, import/export và thống kê dữ liệu.</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-1">
                    <button id="ai-chat-clear" type="button" class="rounded-xl p-2 text-slate-500 transition-all hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800" title="Xóa lịch sử chat" aria-label="Clear chat history">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                    <button id="ai-chat-toggle-size" type="button" class="rounded-xl p-2 text-slate-500 transition-all hover:bg-sky-50 hover:text-sky-700 dark:hover:bg-slate-800" title="Phóng to / Thu gọn" aria-label="Toggle size">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 3H5a2 2 0 00-2 2v3m18-3v3a2 2 0 01-2 2h-3M8 21H5a2 2 0 01-2-2v-3m18 3v-3a2 2 0 00-2-2h-3" />
                        </svg>
                    </button>
                    <button id="ai-chat-close" type="button" class="rounded-xl p-2 text-slate-500 transition-all hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10" title="Đóng" aria-label="Close">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        <div class="shrink-0 border-b border-slate-200/70 px-5 py-4 dark:border-slate-800">
            <div id="ai-chat-hint" class="rounded-[1.6rem] border border-sky-100 bg-white/80 p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/70">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-sky-600 dark:text-sky-300">Gợi ý nhanh</p>
                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                    Hỏi về `users`, `products`, schema, migration, import/export CSV, thống kê số lượng, trạng thái dữ liệu hoặc quan hệ bảng.
                </p>
            </div>
        </div>

        <div id="ai-chat-messages" class="min-h-0 flex-1 space-y-4 overflow-y-auto px-5 py-5">
        </div>

        <form id="ai-chat-form" class="shrink-0 border-t border-slate-200/80 bg-white/70 px-4 py-4 dark:border-slate-800 dark:bg-slate-950/60">
            <div class="flex items-end gap-3 rounded-[1.7rem] border border-slate-200 bg-white px-3 py-3 shadow-sm focus-within:border-sky-400 focus-within:ring-4 focus-within:ring-sky-500/10 dark:border-slate-800 dark:bg-slate-950">
                <textarea id="ai-chat-input" rows="1" autocomplete="off" placeholder="Nhập câu hỏi về database..." class="max-h-32 min-h-[46px] flex-1 resize-none bg-transparent px-2 py-2 text-sm font-medium text-slate-900 outline-none placeholder:text-slate-400 dark:text-slate-100 dark:placeholder:text-slate-500"></textarea>
                <button id="ai-chat-send" type="submit" class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-r from-sky-500 to-blue-600 text-white shadow-lg shadow-sky-500/20 transition-all hover:translate-y-[-1px] active:scale-95">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h12m0 0-4-4m4 4-4 4" />
                    </svg>
                </button>
            </div>
        </form>

        <div id="ai-chat-confirm" class="absolute inset-0 z-10 hidden">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="w-full max-w-sm overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                    <div class="px-5 py-4">
                        <p class="text-sm font-black text-slate-900 dark:text-slate-100">Xóa lịch sử chat?</p>
                        <p class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">Thao tác này chỉ xóa lịch sử lưu trên trình duyệt hiện tại.</p>
                    </div>
                    <div class="flex items-center justify-end gap-2 px-5 pb-5">
                        <button id="ai-chat-confirm-cancel" type="button" class="rounded-2xl px-4 py-2 text-sm font-bold text-slate-600 transition-all hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">Hủy</button>
                        <button id="ai-chat-confirm-ok" type="button" class="rounded-2xl bg-rose-600 px-4 py-2 text-sm font-black text-white shadow-lg shadow-rose-200/70 transition-all active:scale-95">Xóa</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="ai-chat-toast" class="absolute bottom-20 left-1/2 z-10 hidden -translate-x-1/2">
            <div class="rounded-2xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-xl">Đã xóa lịch sử chat</div>
        </div>
    </section>
</div>
@endauth
