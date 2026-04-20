<x-layouts.app title="User Management">

    @php($isAdmin = in_array(strtolower(trim((string) (auth()->user()->role ?? ''))), ['administrator', 'admin'], true))

    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Thành viên hệ thống</h2>
                <p class="text-slate-500 text-sm font-medium mt-1">Quản lý quyền hạn và thông tin người dùng của bạn.</p>
            </div>

            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('users.index') }}" class="relative group" data-users-search>
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" name="q" value="{{ $q ?? request('q') }}" placeholder="Tìm kiếm" class="pl-10 pr-10 py-2.5 bg-white border border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all w-72 shadow-sm">

                    @if(!empty($q ?? request('q')))
                        <a href="{{ route('users.index') }}" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-700" title="Xóa tìm kiếm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </a>
                    @endif
                </form>

                @if ($isAdmin)
                    <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-2xl shadow-lg shadow-indigo-200 transition-all active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Thêm người dùng
                    </a>
                @endif
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-md rounded-[2.5rem] border border-slate-200/60 shadow-[0_20px_50px_rgba(0,0,0,0.04)] overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-8 py-5 text-[11px] font-black text-slate-400 uppercase tracking-[0.15em]">Người dùng</th>
                        <th class="px-6 py-5 text-[11px] font-black text-slate-400 uppercase tracking-[0.15em]">Email</th>
                        <th class="px-6 py-5 text-[11px] font-black text-slate-400 uppercase tracking-[0.15em]">Vai trò</th>
                        <th class="px-8 py-5 text-[11px] font-black text-slate-400 uppercase tracking-[0.15em] text-right">Thao tác</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @foreach($users as $user)
                    @php($role = strtolower(trim((string) ($user->role ?? 'staff'))))
                    @php($avatarUrl = $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&bg=6366f1&color=fff')
                    
                    <tr class="hover:bg-indigo-50/30 transition-colors group">
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-4">
                                <div class="relative">
                                     <img class="w-10 h-10 rounded-2xl border-2 border-white shadow-md group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 object-cover" 
                                        src="{{ $avatarUrl }}" alt="Avatar">
                                    <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full"></span>
                                </div>
                                <div class="cursor-pointer" 
                                     data-user-trigger 
                                     data-name="{{ $user->name }}" 
                                     data-email="{{ $user->email }}" 
                                     data-id="{{ $user->id }}" 
                                     data-role="{{ $role }}"
                                     data-avatar="{{ $avatarUrl }}">
                                    <p class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition-colors tracking-tight">
                                        {{ $user->name }}
                                    </p>
                                    <p class="text-[11px] text-slate-400 font-medium">ID: #{{ $user->id }}</p>
                                </div>
                            </div>
                        </td>
                        
                        <td class="px-6 py-5">
                            <span class="text-sm font-medium text-slate-600 italic">{{ $user->email }}</span>
                        </td>

                        <td class="px-6 py-5">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border
                                {{ $role === 'administrator' || $role === 'admin' ? 'bg-indigo-50 text-indigo-600 border-indigo-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100' }}">
                                {{ $role === 'administrator' || $role === 'admin' ? 'Administrator' : 'Staff' }}
                            </span>
                        </td>

                        <td class="px-8 py-5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if ($isAdmin)
                                    <a href="{{ route('users.edit', $user) }}" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all" title="Chỉnh sửa">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2.0001 2.0001 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>

                                    <button type="button" data-users-delete="1" data-action="{{ route('users.destroy', $user) }}" data-user-name="{{ $user->name }}" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all" title="Xóa thành viên">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($users->hasPages())
            <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100">
                {{ $users->links() }}
            </div>
            @endif
        </div>
    </div>

    <div id="profile-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" id="profile-modal-overlay"></div>
        
        <div class="relative w-full max-w-sm transform overflow-hidden rounded-[2.5rem] bg-white p-8 text-center shadow-2xl transition-all border border-slate-100 animate-in fade-in zoom-in duration-300">
            <div class="flex flex-col items-center">
                <div class="relative mb-4">
                    <img id="p-avatar" src="" class="w-24 h-24 rounded-[2rem] border-4 border-white shadow-xl object-cover">
                    <span class="absolute -bottom-1 -right-1 w-6 h-6 bg-emerald-500 border-4 border-white rounded-full"></span>
                </div>
                
                <h3 id="p-name" class="text-2xl font-black text-slate-900 leading-tight"></h3>
                <p id="p-role-badge" class="mt-2 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border"></p>
                
                <div class="w-full mt-6 space-y-3">
                    <div class="flex items-center gap-3 p-4 rounded-2xl bg-slate-50 border border-slate-100 text-left">
                        <div class="p-2 bg-white rounded-xl shadow-sm text-indigo-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Email</p>
                            <p id="p-email" class="text-sm font-bold text-slate-700 truncate"></p>
                        </div>
                    </div>
                </div>

                <button type="button" id="p-close" class="mt-6 w-full py-4 bg-slate-900 hover:bg-indigo-600 text-white text-sm font-bold rounded-2xl transition-all active:scale-95 shadow-lg shadow-slate-200">
                    Đóng cửa sổ
                </button>
            </div>
        </div>
    </div>

    @if ($isAdmin)
        <div id="users-delete-modal" class="hidden fixed inset-0 z-[70]">
            <div id="users-delete-overlay" class="absolute inset-0 bg-slate-900/30"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="w-full max-w-sm rounded-3xl bg-white border border-slate-200 shadow-2xl overflow-hidden">
                    <div class="px-5 py-4">
                        <p class="text-sm font-black text-slate-900">Xóa người dùng?</p>
                        <p class="mt-1 text-xs font-medium text-slate-500">
                            Bạn sắp xóa <span id="users-delete-name" class="font-black text-slate-700"></span>.
                        </p>
                    </div>
                    <div class="px-5 pb-5 flex items-center justify-end gap-2">
                        <button id="users-delete-cancel" type="button" class="px-4 py-2 rounded-2xl text-sm font-bold text-slate-600 hover:bg-slate-100 transition-all">Hủy</button>
                        <button id="users-delete-confirm" type="button" class="px-4 py-2 rounded-2xl text-sm font-black bg-rose-600 text-white shadow-lg shadow-rose-200 transition-all">Xóa</button>
                    </div>
                    <form id="users-delete-form" method="POST" class="hidden">@csrf @method('DELETE')</form>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Logic cho Profile Modal
            const pModal = document.getElementById('profile-modal');
            const pOverlay = document.getElementById('profile-modal-overlay');
            const pClose = document.getElementById('p-close');
            
            document.querySelectorAll('[data-user-trigger]').forEach(trigger => {
                trigger.addEventListener('click', function() {
                    const d = this.dataset;
                    
                    document.getElementById('p-name').textContent = d.name;
                    document.getElementById('p-email').textContent = d.email;
                    document.getElementById('p-avatar').src = d.avatar;
                    
                    const badge = document.getElementById('p-role-badge');
                    if(d.role === 'administrator' || d.role === 'admin') {
                        badge.textContent = 'Administrator';
                        badge.className = 'mt-2 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border bg-indigo-50 text-indigo-600 border-indigo-100';
                    } else {
                        badge.textContent = 'Staff';
                        badge.className = 'mt-2 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border bg-emerald-50 text-emerald-600 border-emerald-100';
                    }

                    pModal.classList.remove('hidden');
                });
            });

            const closeP = () => pModal.classList.add('hidden');
            pClose.onclick = closeP;
            pOverlay.onclick = closeP;

            // Logic cho Delete Modal (Giữ nguyên logic của bạn)
            const dModal = document.getElementById('users-delete-modal');
            if (dModal) {
                document.querySelectorAll('[data-users-delete]').forEach(btn => {
                    btn.addEventListener('click', function() {
                        document.getElementById('users-delete-name').textContent = this.dataset.userName;
                        document.getElementById('users-delete-form').action = this.dataset.action;
                        dModal.classList.remove('hidden');
                    });
                });
                document.getElementById('users-delete-cancel').onclick = () => dModal.classList.add('hidden');
                document.getElementById('users-delete-confirm').onclick = () => document.getElementById('users-delete-form').submit();
            }
        });
    </script>
</x-layouts.app>