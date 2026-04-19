@props(['title' => 'Authentication'])
<!DOCTYPE html>
<html lang="vi" class="h-full">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }} | Nexus Pro</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full mesh-gradient flex items-center justify-center p-6 antialiased">
    {{ $slot }}
</body>
</html>