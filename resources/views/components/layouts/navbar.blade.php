<div class="h-20 px-8 flex justify-between items-center bg-white/40 backdrop-blur-xl border-b border-slate-200/50">

    <div class="flex items-center gap-6">
        <h1 class="text-xl font-extrabold text-slate-800 tracking-tightest font-heading">
            {{ $title }}<span class="text-indigo-500">.</span>
        </h1>
        
        <div class="hidden md:flex items-center bg-slate-100/50 border border-slate-200/50 px-4 py-2 rounded-2xl group focus-within:bg-white focus-within:ring-4 focus-within:ring-indigo-500/10 transition-all">
            <svg class="w-4 h-4 text-slate-400 group-focus-within:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" placeholder="Tìm kiếm..." class="bg-transparent border-none text-xs font-bold focus:ring-0 placeholder-slate-400 w-48">
        </div>
    </div>

    <div class="flex items-center gap-6">
        <button class="relative p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <span class="absolute top-2 right-2.5 w-2 h-2 bg-rose-500 border-2 border-white rounded-full"></span>
        </button>

        <div class="h-8 w-[1px] bg-slate-200"></div>

        <div class="flex items-center gap-4 group cursor-pointer">
            @auth
                <div class="text-right hidden sm:block">
                    <a href="{{ route('profile.edit') }}" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                        <p class="text-sm font-black text-slate-800 leading-none group-hover:text-indigo-600 transition-colors">
                            {{ auth()->user()->name }}
                        </p>
                        Cài đặt tài khoản
                    </a>
                </div>
                
                <div class="relative">
                    <img class="w-10 h-10 rounded-2xl border-2 border-white shadow-md group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 object-cover" 
                        src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&bg=6366f1&color=fff' }}" 
                        alt="Avatar">
                    <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 border-2 border-white rounded-full shadow-sm"></div>
                </div>

                <form action="{{ route('logout') }}" method="POST" class="ml-2">
                    @csrf
                    <button type="submit" class="flex items-center justify-center w-10 h-10 bg-slate-100 text-slate-500 hover:bg-rose-50 hover:text-rose-600 rounded-xl transition-all group shadow-sm active:scale-90">
                        <svg class="w-5 h-5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            @else
                <div class="text-right">
                    <a href="{{ route('login') }}" class="text-sm font-bold text-indigo-600 hover:underline">Đăng nhập</a>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-slate-200 flex items-center justify-center text-slate-400">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/></svg>
                </div>
            @endauth
        </div>
    </div>
</div>