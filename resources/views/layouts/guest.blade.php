<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Team Task Manager') }} - @yield('title')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>

    <!-- Scripts -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    @stack('styles')
</head>
<body class="font-sans antialiased">
    <!-- Page Content -->
    <main>
        @if (session('status'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-transition 
                 x-init="setTimeout(() => show = false, 5000)"
                 class="fixed top-4 right-4 z-50">
                <div class="p-4 rounded-lg shadow-lg 
                    @if (session('status-type') === 'success') bg-green-100 text-green-700
                    @elseif (session('status-type') === 'error') bg-red-100 text-red-700
                    @else bg-blue-100 text-blue-700 @endif">
                    {{ session('status') }}
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-auto py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center text-sm text-gray-500">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <div class="mt-2 space-x-4">
                    <a href="{{ route('terms') }}" class="hover:text-gray-700">Terms of Service</a>
                    <a href="{{ route('privacy') }}" class="hover:text-gray-700">Privacy Policy</a>
                    <a href="{{ route('contact') }}" class="hover:text-gray-700">Contact Us</a>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
