<div class="w-64 bg-slate-900 shadow-xl h-screen flex flex-col p-5 hidden md:block">
    <div class="flex items-center gap-3 mb-10 px-2">
        <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center shadow-lg shadow-indigo-500/50">
            <span class="text-white font-bold italic">M</span>
        </div>
        <h2 class="text-xl font-extrabold text-white tracking-tight">MyApp</h2>
    </div>

    <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-widest px-2 mb-4">Main Menu</p>

    <nav class="flex-1">
        <ul class="space-y-1.5">
            <li>
                <a href="/dashboard" 
                   class="flex items-center gap-3 p-2.5 rounded-xl transition-all duration-200 {{ request()->is('dashboard*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="font-medium">Dashboard</span>
                </a>
            </li>

            <li>
                <a href="{{ route('users.index') }}" 
                   class="flex items-center gap-3 p-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('users.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="font-medium">Users</span>
                </a>
            </li>

            <li>
                <a href="#" 
                   class="flex items-center gap-3 p-2.5 rounded-xl transition-all duration-200 {{ request()->is('tasks*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <span class="font-medium">Tasks</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="mt-auto pt-6 border-t border-slate-800 px-2">
        <p class="text-[10px] text-slate-500 font-medium tracking-tight">v1.0 - Laravel</p>
    </div>
</div>