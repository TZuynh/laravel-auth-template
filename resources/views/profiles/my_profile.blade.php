<x-layouts.app title="My Profile">
    <div class="max-w-4xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-8 duration-700">
        
        @if (session('success'))
            <div id="flash-message" 
                 class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 rounded-2xl text-sm font-bold shadow-sm transition-all duration-500 ease-in-out animate-in fade-in zoom-in sticky top-4 z-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="bg-emerald-500 text-white p-1 rounded-full">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        {{ session('success') }}
                    </div>
                    <button onclick="closeFlashMessage()" class="text-emerald-400 hover:text-emerald-600 transition-colors p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-12 gap-8">
            @csrf
            @method('PATCH')

            <div class="col-span-12 lg:col-span-4 space-y-6">
                <div class="bg-white/80 backdrop-blur-xl p-8 rounded-[2.5rem] border border-slate-200/60 shadow-xl flex flex-col items-center text-center">
                    <h3 class="text-lg font-black text-slate-800 mb-6">Ảnh đại diện</h3>
                    
                    <div class="relative group">
                        <img id="avatar-preview" 
                             class="w-40 h-40 rounded-[2.5rem] object-cover border-4 border-white shadow-2xl transition-transform group-hover:scale-105" 
                             src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&bg=6366f1&color=fff' }}">
                        
                        <label for="avatar-input" class="absolute inset-0 flex items-center justify-center bg-indigo-600/60 rounded-[2.5rem] opacity-0 group-hover:opacity-100 cursor-pointer transition-opacity">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </label>
                        <input type="file" id="avatar-input" name="avatar" class="hidden" onchange="previewImage(this)">
                    </div>

                    <p class="text-xs font-bold text-slate-400 mt-6 uppercase tracking-widest leading-relaxed">
                        Chạm vào ảnh để thay đổi<br>(JPG, PNG max 2MB)
                    </p>
                </div>
            </div>

            <div class="col-span-12 lg:col-span-8 space-y-6">
                <div class="bg-white/80 backdrop-blur-xl p-10 rounded-[2.5rem] border border-slate-200/60 shadow-xl">
                    <h3 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3">
                        <span class="w-2 h-6 bg-indigo-500 rounded-full"></span>
                        Thông tin cá nhân
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Họ và tên</label>
                            <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" 
                                   class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-bold text-slate-700">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Địa chỉ Email</label>
                            <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" 
                                   class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-bold text-slate-700">
                        </div>
                    </div>

                    <div class="mt-10 pt-10 border-t border-slate-100">
                        <h3 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3">
                            <span class="w-2 h-6 bg-rose-500 rounded-full"></span>
                            Đổi mật khẩu
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Mật khẩu mới</label>
                                <input type="password" name="password" placeholder="••••••••" 
                                       class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-bold">
                            </div>

                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Xác nhận mật khẩu</label>
                                <input type="password" name="password_confirmation" placeholder="••••••••" 
                                       class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-bold">
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 flex justify-end">
                        <button type="submit" class="px-10 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-2xl shadow-xl shadow-indigo-200 transition-all active:scale-95">
                            Cập nhật hồ sơ
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        // 1. Hàm xem trước ảnh khi chọn file
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatar-preview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // 2. Hàm đóng thông báo thủ công
        function closeFlashMessage() {
            const flash = document.getElementById('flash-message');
            if (flash) {
                flash.style.opacity = '0';
                flash.style.transform = 'translateY(-20px) scale(0.95)';
                setTimeout(() => {
                    flash.remove();
                }, 500);
            }
        }

        // 3. Tự động đóng thông báo sau 3 giây
        document.addEventListener('DOMContentLoaded', function() {
            const flash = document.getElementById('flash-message');
            if (flash) {
                setTimeout(() => {
                    closeFlashMessage();
                }, 3000); // 3000ms = 3 giây
            }
        });
    </script>
</x-layouts.app>