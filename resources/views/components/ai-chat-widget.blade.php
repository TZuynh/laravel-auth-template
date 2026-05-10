@auth
<div id="ai-chat-root" class="fixed bottom-5 right-5 z-[70]">
    <button
        id="ai-chat-fab"
        type="button"
        class="fixed bottom-5 right-5 z-[72] inline-flex h-16 w-16 items-center justify-center rounded-full bg-slate-950 text-white shadow-2xl ring-2 ring-white transition hover:scale-105"
        aria-label="Open Admin Assistant"
    >
        <svg data-ai-icon-open class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M8 7h8M7 11h10M8 15h8"/>
            <circle cx="12" cy="12" r="9"/>
        </svg>
        <svg data-ai-icon-close class="hidden h-8 w-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
        </svg>
    </button>

    <section
        id="ai-chat-panel"
        class="absolute bottom-20 right-0 hidden h-[min(82vh,760px)] w-[min(94vw,560px)] flex-col overflow-hidden rounded-[2rem] border border-slate-200 bg-slate-50 shadow-[0_30px_90px_rgba(15,23,42,0.22)]"
    >
        <header class="flex shrink-0 items-center justify-between bg-slate-900 px-5 py-4 text-white">
            <div class="flex items-center gap-3">
                <span class="relative inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M8 7h8M7 11h10M8 15h8"/>
                    </svg>
                    <span class="absolute -bottom-0.5 -right-0.5 h-3.5 w-3.5 rounded-full border-2 border-slate-900 bg-emerald-400"></span>
                </span>
                <div>
                    <p class="text-lg font-black leading-tight">Admin Assistant</p>
                    <p class="mt-1 flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.16em] text-slate-200">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        Internal Engine
                    </p>
                </div>
            </div>
            <button id="ai-chat-close" type="button" class="rounded-xl p-2 text-slate-300 hover:bg-white/10 hover:text-white" aria-label="Close">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </header>

        <div id="ai-chat-messages" class="min-h-0 flex-1 space-y-5 overflow-y-auto px-5 py-6"></div>

        <div class="shrink-0 border-t border-slate-200 bg-white px-5 py-4">
            <div id="ai-chat-quick-actions" class="mb-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                <button type="button" class="rounded-xl bg-blue-50 px-4 py-3 text-left text-sm font-black text-slate-700 hover:bg-blue-100" data-quick-prompt="Doanh thu hôm nay bao nhiêu?">Doanh thu hôm nay</button>
                <button type="button" class="rounded-xl bg-blue-50 px-4 py-3 text-left text-sm font-black text-slate-700 hover:bg-blue-100" data-quick-prompt="Hàng nào sắp hết?">Hàng sắp hết</button>
                <button type="button" class="rounded-xl bg-blue-50 px-4 py-3 text-left text-sm font-black text-slate-700 hover:bg-blue-100 sm:col-span-2" data-quick-prompt="Tra cứu hợp đồng HD-2026-001">Tra cứu Hợp đồng</button>
            </div>

            <form id="ai-chat-form" class="flex items-end gap-2">
                <div class="flex min-h-[52px] flex-1 items-end rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 focus-within:border-blue-400 focus-within:ring-4 focus-within:ring-blue-500/10">
                    <textarea id="ai-chat-input" rows="1" autocomplete="off" placeholder="Tra cứu nhanh..." class="max-h-28 min-h-[34px] flex-1 resize-none bg-transparent py-2 text-sm font-semibold text-slate-900 outline-none placeholder:text-slate-400"></textarea>
                    <button id="ai-chat-voice" type="button" class="mb-1 rounded-xl p-2 text-slate-400 hover:bg-white hover:text-blue-600" aria-label="Voice input">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 14a3 3 0 0 0 3-3V6a3 3 0 1 0-6 0v5a3 3 0 0 0 3 3z"/><path d="M19 11a7 7 0 0 1-14 0M12 18v4M8 22h8"/></svg>
                    </button>
                </div>
                <button id="ai-chat-send" type="submit" class="inline-flex h-[52px] w-[52px] items-center justify-center rounded-2xl bg-blue-500 text-white shadow-lg shadow-blue-500/20 hover:bg-blue-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m22 2-7 20-4-9-9-4z"/></svg>
                </button>
            </form>
        </div>

        <div id="ai-chat-toast" class="absolute bottom-24 left-1/2 z-10 hidden -translate-x-1/2 rounded-2xl bg-slate-950 px-4 py-2 text-xs font-black text-white shadow-xl"></div>
    </section>
</div>
@endauth
