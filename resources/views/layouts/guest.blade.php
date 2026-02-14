<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name'))</title>

        {{-- SEO Meta Tags --}}
        <meta name="description" content="@yield('meta_description', config('app.description'))">
        <meta name="keywords" content="@yield('meta_keywords', config('app.keywords'))">
        <meta name="author" content="{{ config('app.name') }}">
        <link rel="canonical" href="@yield('canonical_url', url()->current())">

        {{-- Open Graph / Facebook --}}
        <meta property="og:type" content="website">
        <meta property="og:url" content="@yield('og_url', url()->current())">
        <meta property="og:title" content="@yield('og_title', config('app.name'))">
        <meta property="og:description" content="@yield('og_description', config('app.description'))">
        <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">

        {{-- Twitter Card --}}
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="@yield('twitter_title', config('app.name'))">
        <meta name="twitter:description" content="@yield('twitter_description', config('app.description'))">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @php
            $manifestPath = public_path('build/manifest.json');
            $cssPath = public_path('build/app.css');
            $hasBuiltAssets = false;
            
            if (file_exists($manifestPath) && file_exists($cssPath)) {
                $cssContent = file_get_contents($cssPath);
                // Check if CSS file contains actual CSS (not just a comment)
                // Look for CSS rules (contains '{' or '@' directives like @media, @keyframes, etc.)
                $hasBuiltAssets = filesize($cssPath) > 500 && 
                                 (strpos($cssContent, '{') !== false || 
                                  preg_match('/@(media|keyframes|supports|import)/', $cssContent));
            }
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
    <body class="font-sans text-gray-900 antialiased bg-white">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div class="mb-6">
                <a href="/" class="text-2xl font-bold text-black">
                    Recipes
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-8 card">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
