@props(['title' => 'Authentication'])
@php($appName = (string) env('SITE_NAME', 'Owl Agency'))
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full">
<head>
    <meta charset="UTF-8">
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
<body class="h-full mesh-gradient flex items-center justify-center p-6 antialiased bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <x-flash-banner />
    {{ $slot }}
</body>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</html>
