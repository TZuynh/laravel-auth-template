<x-layouts.auth title="Tạo tài khoản">
    <div class="max-w-md w-full px-4">
        {{-- Card Container với hiệu ứng Glassmorphism --}}
        <div class="bg-white/90 backdrop-blur-xl rounded-[2rem] shadow-[0_20px_50px_rgba(0,0,0,0.1)] border border-white/20 p-8 md:p-10 transition-all duration-500 hover:shadow-[0_20px_60px_rgba(99,102,241,0.15)]">
            
            {{-- Header --}}
            <div class="text-center mb-10">
                <h2 class="text-4xl font-extrabold text-slate-900 tracking-tight font-heading">
                    Join Us<span class="text-indigo-600">.</span>
                </h2>
                <p class="text-slate-500 mt-3 font-medium">Bắt đầu hành trình cùng Nexus Core</p>
            </div>

            {{-- Error Alerts --}}
            @if($errors->any())
                <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-700 text-xs rounded-r-xl animate-pulse">
                    <p class="font-bold uppercase tracking-widest mb-1">Có lỗi xảy ra:</p>
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Form --}}
            <form action="{{ route('register.post') }}" method="POST" class="space-y-5">
                @csrf
                
                {{-- Full Name --}}
                <div class="group">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1 transition-colors group-focus-within:text-indigo-500">Full Name</label>
                    <div class="relative">
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Nhập họ và tên"
                               class="w-full bg-slate-100/50 border-transparent px-4 py-3.5 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none border text-sm font-semibold">
                    </div>
                </div>

                {{-- Email --}}
                <div class="group">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1 transition-colors group-focus-within:text-indigo-500">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="example@domain.com"
                           class="w-full bg-slate-100/50 border-transparent px-4 py-3.5 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none border text-sm font-semibold">
                </div>

                {{-- Password Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="group">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1 transition-colors group-focus-within:text-indigo-500">Password</label>
                        <input type="password" name="password" required placeholder="••••••••"
                               class="w-full bg-slate-100/50 border-transparent px-4 py-3.5 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none border text-sm font-semibold">
                    </div>
                    <div class="group">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1 transition-colors group-focus-within:text-indigo-500">Confirm</label>
                        <input type="password" name="password_confirmation" required placeholder="••••••••"
                               class="w-full bg-slate-100/50 border-transparent px-4 py-3.5 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none border text-sm font-semibold">
                    </div>
                </div>

                {{-- Submit Button --}}
                <button type="submit" 
                        class="w-full bg-slate-900 hover:bg-indigo-600 text-white font-bold py-4 rounded-2xl shadow-xl shadow-indigo-100 transition-all duration-300 transform active:scale-[0.98] flex items-center justify-center gap-2 group">
                    <span>Tạo tài khoản</span>
                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </button>
            </form>

            {{-- Footer --}}
            <div class="mt-10 text-center">
                <p class="text-sm text-slate-500 font-medium">
                    Đã có tài khoản? 
                    <a href="{{ route('login') }}" class="text-indigo-600 font-bold hover:text-indigo-700 transition-colors ml-1 underline decoration-2 underline-offset-4 decoration-indigo-100 hover:decoration-indigo-500">
                        Đăng nhập
                    </a>
                </p>
            </div>
        </div>

        {{-- Footer Note --}}
        <p class="text-center text-slate-400 text-[10px] uppercase tracking-[0.3em] mt-8 font-bold">Nexus Core Security System</p>
    </div>
</x-layouts.auth>