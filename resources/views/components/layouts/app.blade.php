@props(['title' => 'Dashboard'])
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-locale" content="{{ app()->getLocale() }}">
    <title>{{ $title }} | Nexus Pro</title>
    <script>
        (function () {
            try {
                if (localStorage.getItem('theme') === 'dark') {
                    document.documentElement.classList.add('dark');
                }
            } catch (error) {}
        })();
    </script>
    {{-- Đảm bảo bạn đã cài đặt Tailwind và chạy npm run dev --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full mesh-gradient antialiased overflow-hidden bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <div class="flex h-screen w-full">
        <aside class="z-50 border-r border-slate-200/50 bg-white/40 backdrop-blur-md flex-shrink-0 dark:border-slate-800/80 dark:bg-slate-950/80">
            {{-- Laravel tìm file tại: resources/views/components/layouts/sidebar.blade.php --}}
            <x-layouts.sidebar />
        </aside>

        <div class="flex-1 flex flex-col min-w-0 relative">
            <header class="sticky top-0 z-40 bg-white/60 backdrop-blur-xl border-b border-slate-200/60 dark:border-slate-800 dark:bg-slate-950/70">
                {{-- Laravel tìm file tại: resources/views/components/layouts/navbar.blade.php --}}
                <x-layouts.navbar :title="$title" />
            </header>

            <main class="flex-1 overflow-y-auto p-6 md:p-10 dark:bg-slate-950">
                <div class="max-w-[1500px] mx-auto">
                    <x-flash-banner />
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <x-ai-chat-widget />
</body>
</html>
