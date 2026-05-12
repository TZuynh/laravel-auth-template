@props(['title' => 'Dashboard'])
@php($appName = (string) env('SITE_NAME', 'Owl Agency'))
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-locale" content="{{ app()->getLocale() }}">
    <title>{{ $title }} | {{ $appName }}</title>
    <script>
        (function () {
            try {
                if (localStorage.getItem('theme') === 'dark') {
                    document.documentElement.classList.add('dark');
                }
            } catch (error) {}
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full overflow-hidden bg-slate-100 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
    <div id="app-shell" class="flex h-screen w-full">
        <aside id="app-sidebar" class="z-50 shrink-0">
            <x-layouts.sidebar />
        </aside>

        <div class="relative flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-40">
                <x-layouts.navbar :title="$title" />
            </header>

            <main class="app-main-content flex-1 overflow-y-auto bg-[#f6f8fb] p-4 md:p-6 dark:bg-slate-950">
                <div class="mx-auto w-full max-w-[1760px]">
                    <x-flash-banner />
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <x-ai-chat-widget />
</body>
</html>
