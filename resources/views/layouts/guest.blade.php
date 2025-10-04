<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Welcome')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Fallback CSS jika Vite tidak berjalan -->
    <style>
        /* Fallback styling jika Tailwind belum load */
        .min-h-screen { min-height: 100vh; }
        .bg-gradient-to-br { background: linear-gradient(to bottom right, #22d3ee, #3b82f6, #9333ea); }
        .backdrop-blur-xl { backdrop-filter: blur(24px); }
        .bg-white\/20 { background-color: rgba(255, 255, 255, 0.2); }
        .border-white\/30 { border-color: rgba(255, 255, 255, 0.3); }
        .text-white { color: white; }
        .rounded-3xl { border-radius: 1.5rem; }
        .p-8 { padding: 2rem; }
        .shadow-2xl { box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
    </style>
</head>
<body class="font-inter antialiased">
    @yield('content')
    
    <!-- Debug info -->
    <script>
        console.log('Guest layout loaded');
        console.log('Alpine available:', typeof Alpine !== 'undefined');
    </script>
</body>
</html>