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
            <div class="group" x-data="{ show: false }">
                <div class="flex justify-between items-center mb-2 ml-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] transition-colors group-focus-within:text-indigo-500">
                        Security Key
                    </label>
                    <a href="#" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-700 tracking-widest uppercase transition-all hover:tracking-[0.3em]">Quên?</a>
                </div>
                
                <div class="relative group/input">
                    <input :type="show ? 'text' : 'password'" 
                        name="password" 
                        required 
                        placeholder="••••••••"
                        class="w-full bg-slate-100/50 border border-transparent px-5 py-4 pr-14 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all duration-300 outline-none text-sm font-semibold tracking-widest">
                    
                    {{-- Nút Toggle chuyên nghiệp --}}
                    <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center pr-2">
                        <button type="button" 
                                @click="show = !show" 
                                class="relative p-2 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all duration-300 active:scale-90 focus:outline-none overflow-hidden group/eye"
                                title="Hiển thị mật khẩu">
                            
                            {{-- Hiệu ứng vòng tròn lan tỏa khi hover (optional) --}}
                            <div class="absolute inset-0 bg-indigo-500/0 group-hover/eye:bg-indigo-500/5 transition-colors duration-300"></div>

                            {{-- Container Icon với hiệu ứng Xoay --}}
                            <div class="relative w-5 h-5 flex items-center justify-center transform transition-transform duration-500" :class="show ? 'rotate-180' : ''">
                                
                                {{-- Icon Mắt Mở --}}
                                <svg x-show="!show" 
                                    class="w-5 h-5 transition-all duration-300" 
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>

                                {{-- Icon Mắt Gạch (Ẩn) --}}
                                <svg x-show="show" x-cloak 
                                    class="w-5 h-5 transition-all duration-300 text-indigo-600" 
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </div>
                        </button>
                    </div>
                </div>
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