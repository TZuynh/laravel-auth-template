<x-layouts.app :title="__('messages.erp.profile.title')">
    <div class="mx-auto max-w-5xl space-y-6 animate-in fade-in slide-in-from-bottom-8 duration-700">
        
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

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-12 gap-6">
            @csrf
            @method('PATCH')

            <div class="col-span-12 space-y-6 lg:col-span-4">
                <div class="flex flex-col items-center rounded-3xl border border-slate-200/70 bg-white/90 p-7 text-center shadow-sm backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/80">
                    <h3 class="mb-5 text-lg font-black text-slate-800 dark:text-slate-100">{{ __('messages.erp.profile.avatar') }}</h3>
                    
                    <div class="relative group">
                        <img id="avatar-preview" 
                             class="h-36 w-36 rounded-3xl border-4 border-white object-cover shadow-xl transition-transform group-hover:scale-105 dark:border-slate-950" 
                             src="{{ auth()->user()->avatar_url }}"
                             alt="{{ auth()->user()->name }}">
                        
                        <label for="avatar-input" class="absolute inset-0 flex cursor-pointer items-center justify-center rounded-3xl bg-indigo-600/60 opacity-0 transition-opacity group-hover:opacity-100">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </label>
                        <input type="file" id="avatar-input" name="avatar" class="hidden" onchange="previewImage(this)">
                    </div>

                    <p class="mt-5 max-w-[220px] text-xs font-bold uppercase leading-relaxed tracking-widest text-slate-400">
                        {{ __('messages.erp.profile.avatar_hint') }}
                    </p>
                </div>
            </div>

            <div class="col-span-12 lg:col-span-8 space-y-6">
                <div class="rounded-3xl border border-slate-200/70 bg-white/90 p-7 shadow-sm backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/80 lg:p-8">
                    <h3 class="mb-7 flex items-center gap-3 text-xl font-black text-slate-800 dark:text-slate-100">
                        <span class="w-2 h-6 bg-indigo-500 rounded-full"></span>
                        {{ __('messages.erp.profile.personal_info') }}
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="ml-1 text-[11px] font-black uppercase tracking-widest text-slate-400">{{ __('messages.erp.profile.full_name') }}</label>
                            <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" 
                                   class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-3.5 font-bold text-slate-700 transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100">
                        </div>

                        <div class="space-y-2">
                            <label class="ml-1 text-[11px] font-black uppercase tracking-widest text-slate-400">{{ __('messages.erp.profile.email') }}</label>
                            <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" 
                                   class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-3.5 font-bold text-slate-700 transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100">
                        </div>
                    </div>

                    <div class="mt-10 pt-10 border-t border-slate-100">
                        <h3 class="mb-7 flex items-center gap-3 text-xl font-black text-slate-800 dark:text-slate-100">
                            <span class="w-2 h-6 bg-rose-500 rounded-full"></span>
                            {{ __('messages.erp.profile.change_password') }}
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="ml-1 text-[11px] font-black uppercase tracking-widest text-slate-400">{{ __('messages.erp.profile.new_password') }}</label>
                                <input type="password" name="password" placeholder="••••••••" 
                                       class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-3.5 font-bold transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100">
                            </div>

                            <div class="space-y-2">
                                <label class="ml-1 text-[11px] font-black uppercase tracking-widest text-slate-400">{{ __('messages.erp.profile.confirm_password') }}</label>
                                <input type="password" name="password_confirmation" placeholder="••••••••" 
                                       class="w-full rounded-2xl border border-slate-100 bg-slate-50 px-5 py-3.5 font-bold transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100">
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 flex justify-end">
                        <button type="submit" class="rounded-2xl bg-indigo-600 px-8 py-3.5 font-black text-white shadow-lg shadow-indigo-200 transition-all hover:bg-indigo-700 active:scale-95 dark:shadow-none">
                            {{ __('messages.erp.profile.save') }}
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
