<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Recipes') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @php
            $manifestPath = public_path('build/manifest.json');
            $cssPath = public_path('build/app.css');
            $hasBuiltAssets = file_exists($manifestPath) && 
                             file_exists($cssPath) && 
                             filesize($cssPath) > 100; // Check if file has actual content
        @endphp
        
        @if($hasBuiltAssets)
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            {{-- CDN fallback for development when Vite build is not available --}}
            <script src="https://cdn.tailwindcss.com"></script>
            <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        @endif
        
        <!-- Custom Theme CSS (loads after Tailwind to override) -->
        <link rel="stylesheet" href="{{ asset('css/custom-theme.css') }}">
    </head>
    <body class="font-sans antialiased bg-white">
        <div class="min-h-screen">
            @include('layouts.navigation')
            
            <!-- Add padding-top to account for fixed navigation -->
            <div class="pt-16">

            <!-- Page Content -->
            <main class="animate-fade-in">
                {{ $slot }}
            </main>
            </div>
        </div>
    </body>
</html>
