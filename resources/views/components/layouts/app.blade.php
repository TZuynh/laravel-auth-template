@props(['title' => 'Dashboard'])
<!DOCTYPE html>
<html lang="vi" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} | Nexus Pro</title>
    {{-- Đảm bảo bạn đã cài đặt Tailwind và chạy npm run dev --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full mesh-gradient antialiased overflow-hidden">
    <div class="flex h-screen w-full">
        <aside class="z-50 border-r border-slate-200/50 bg-white/40 backdrop-blur-md flex-shrink-0">
            {{-- Laravel tìm file tại: resources/views/components/layouts/sidebar.blade.php --}}
            <x-layouts.sidebar />
        </aside>

        <div class="flex-1 flex flex-col min-w-0 relative">
            <header class="sticky top-0 z-40 bg-white/60 backdrop-blur-xl border-b border-slate-200/60">
                {{-- Laravel tìm file tại: resources/views/components/layouts/navbar.blade.php --}}
                <x-layouts.navbar :title="$title" />
            </header>

            <main class="flex-1 overflow-y-auto p-6 md:p-10">
                <div class="max-w-[1500px] mx-auto">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <x-ai-chat-widget />
</body>
</html>
