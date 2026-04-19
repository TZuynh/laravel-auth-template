@auth
<div id="ai-chat-root" class="fixed bottom-6 right-6 z-[60]">
  <style>
    @keyframes pulse-border {
        0%, 100% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.7); }
        50% { box-shadow: 0 0 0 10px rgba(99, 102, 241, 0); }
    }
    .animate-pulse-border {
        animation: pulse-border 2s infinite;
    }
</style>

<button id="ai-chat-fab"
        type="button"
        class="group fixed bottom-6 right-6 z-50 inline-flex items-center justify-center w-16 h-16 rounded-full 
               bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 
               text-white shadow-2xl shadow-indigo-500/50 
               transition-all duration-300 ease-out
               hover:scale-110 hover:shadow-indigo-500/80 active:scale-95
               animate-pulse-border"
        aria-label="Open AI Chat">
    
    <svg class="w-8 h-8 transition-transform group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v1m0 3v1m0 3v1" />
    </svg>
    
    <span class="absolute top-1 right-1 flex h-3 w-3">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-3 w-3 bg-indigo-500"></span>
    </span>
</button>

    <section id="ai-chat-panel"
        class="hidden absolute bottom-16 right-0 w-[360px] h-[480px] bg-white/90 backdrop-blur-xl border border-slate-200/70 rounded-[1.75rem] shadow-2xl overflow-hidden">
        <header class="flex items-center justify-between px-4 py-3 border-b border-slate-200/70 bg-white/70">
            <div class="min-w-0">
                <p class="text-sm font-black text-slate-900 tracking-tight truncate">AI Chat (Database)</p>
                <p class="text-[11px] font-bold text-slate-400 truncate">Chỉ trả lời liên quan database</p>
            </div>
            <div class="flex items-center gap-2">
                <button id="ai-chat-clear"
                    type="button"
                    class="p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-all"
                    title="Xóa lịch sử chat"
                    aria-label="Clear chat history">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
                <button id="ai-chat-toggle-size"
                    type="button"
                    class="p-2 rounded-xl text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition-all"
                    title="Phóng to / Thu gọn"
                    aria-label="Toggle size">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 3H5a2 2 0 00-2 2v3m18-3v3a2 2 0 01-2 2h-3M8 21H5a2 2 0 01-2-2v-3m18 3v-3a2 2 0 00-2-2h-3" />
                    </svg>
                </button>
                <button id="ai-chat-close"
                    type="button"
                    class="p-2 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-all"
                    title="Đóng"
                    aria-label="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </header>

        <div id="ai-chat-messages" class="h-[calc(100%-112px)] overflow-y-auto p-4 space-y-3">
            <div id="ai-chat-hint" class="text-xs text-slate-500 bg-slate-50 border border-slate-200/70 rounded-2xl p-3">
                Hỏi về bảng/cột/role/users/migration… Nếu hỏi ngoài database, mình sẽ từ chối.
            </div>
        </div>

        <form id="ai-chat-form" class="h-14 border-t border-slate-200/70 bg-white/70 px-3 flex items-center gap-2">
            <input id="ai-chat-input"
                type="text"
                autocomplete="off"
                placeholder="Nhập câu hỏi về database…"
                class="flex-1 px-4 py-2.5 rounded-2xl bg-white border border-slate-200 text-sm font-medium focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500" />
            <button id="ai-chat-send" type="submit"
                class="inline-flex items-center justify-center w-10 h-10 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg shadow-indigo-200/70 transition-all active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7" />
                </svg>
            </button>
        </form>

        {{-- Confirm popup --}}
        <div id="ai-chat-confirm" class="hidden absolute inset-0 z-10">
            <div class="absolute inset-0 bg-slate-900/30"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="w-full max-w-sm rounded-3xl bg-white border border-slate-200 shadow-2xl overflow-hidden">
                    <div class="px-5 py-4">
                        <p class="text-sm font-black text-slate-900">Xóa lịch sử chat?</p>
                        <p class="mt-1 text-xs font-medium text-slate-500">Hành động này chỉ xóa trên trình duyệt của bạn.</p>
                    </div>
                    <div class="px-5 pb-5 flex items-center justify-end gap-2">
                        <button id="ai-chat-confirm-cancel" type="button"
                            class="px-4 py-2 rounded-2xl text-sm font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-all">
                            Hủy
                        </button>
                        <button id="ai-chat-confirm-ok" type="button"
                            class="px-4 py-2 rounded-2xl text-sm font-black bg-rose-600 hover:bg-rose-700 text-white shadow-lg shadow-rose-200/70 transition-all active:scale-95">
                            Xóa
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Toast --}}
        <div id="ai-chat-toast" class="hidden absolute bottom-16 left-1/2 -translate-x-1/2 z-10">
            <div class="px-4 py-2 rounded-2xl bg-slate-900 text-white text-xs font-bold shadow-xl">
                Đã xóa lịch sử chat
            </div>
        </div>
    </section>
</div>
@endauth
