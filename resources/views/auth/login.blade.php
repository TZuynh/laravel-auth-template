<x-layouts.auth title="Đăng nhập">
    <div class="max-w-md w-full px-4">
        {{-- Card Container với hiệu ứng Glassmorphism & Shadow Layer --}}
        <div class="bg-white/90 backdrop-blur-xl rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.1)] border border-white/20 p-10 transition-all duration-500 hover:shadow-[0_20px_60px_rgba(99,102,241,0.12)]">

            {{-- Header --}}
            <div class="text-center mb-10">
                <div class="inline-block p-3 rounded-2xl bg-indigo-50 text-indigo-600 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h2 class="text-4xl font-extrabold text-slate-900 tracking-tight font-heading">
                    Welcome back<span class="text-indigo-600">.</span>
                </h2>
                <p class="text-slate-500 mt-3 font-medium text-sm">Vui lòng đăng nhập để quản lý hệ thống</p>
            </div>

            {{-- Hiển thị lỗi --}}
            @if($errors->any())
                <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-700 text-xs rounded-r-2xl animate-in fade-in slide-in-from-top-1">
                    <p class="font-bold uppercase tracking-widest mb-1">Xác thực thất bại:</p>
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Form Đăng nhập --}}
            <form action="{{ route('login.post') }}" method="POST" class="space-y-6">
                @csrf

                {{-- Email Field --}}
                <div class="group">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-2 ml-2 transition-colors group-focus-within:text-indigo-500">
                        Email Address
                    </label>
                    <div class="relative">
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="yourname@domain.com"
                               class="w-full bg-slate-100/50 border border-transparent px-5 py-4 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none text-sm font-semibold">
                    </div>
                </div>

                {{-- Password Field --}}
                <div class="group">
                    <div class="flex justify-between items-center mb-2 ml-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] transition-colors group-focus-within:text-indigo-500">
                            Security Key
                        </label>
                        <a href="#" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-700 tracking-widest uppercase">Quên?</a>
                    </div>
                    <input type="password" name="password" required placeholder="••••••••"
                           class="w-full bg-slate-100/50 border border-transparent px-5 py-4 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none text-sm font-semibold">
                </div>

                {{-- Nút Đăng nhập --}}
                <button type="submit" 
                        class="w-full bg-slate-900 hover:bg-indigo-600 text-white font-bold py-4 rounded-2xl shadow-xl shadow-indigo-100 transition-all duration-300 transform active:scale-[0.98] flex items-center justify-center gap-3 group">
                    <span>Sign In</span>
                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>

            {{-- Chuyển sang Register --}}
            <div class="mt-10 text-center">
                <p class="text-sm text-slate-500 font-medium">
                    Chưa có quyền truy cập? 
                    <a href="{{ route('register') }}" class="text-indigo-600 font-bold hover:text-indigo-700 transition-colors ml-1 underline decoration-2 underline-offset-4 decoration-indigo-100 hover:decoration-indigo-500">
                        Đăng ký ngay
                    </a>
                </p>
            </div>
        </div>

        {{-- Footer Branding --}}
        <div class="flex items-center justify-center gap-4 mt-8">
            <span class="w-8 h-[1px] bg-slate-200"></span>
            <p class="text-slate-400 text-[10px] uppercase tracking-[0.3em] font-bold">Nexus Core v3.0</p>
            <span class="w-8 h-[1px] bg-slate-200"></span>
        </div>
    </div>
</x-layouts.auth>