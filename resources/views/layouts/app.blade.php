<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-50">
            <div class="flex min-h-screen">
                <!-- Sidebar -->
                <aside class="hidden lg:block w-64 bg-white border-r border-gray-200">
                    @include('layouts.navigation')
                </aside>

                <!-- Main area -->
                <div class="flex-1 flex flex-col min-w-0">
                    <!-- Top header line -->
                    @isset($header)
                        <header class="bg-gradient-to-r from-blue-500 to-purple-600 shadow-lg">
                            <div class="max-w-7xl mx-auto h-16 px-4 sm:px-6 lg:px-8 text-white flex items-center">
                                <div class="w-full">
                                    {{ $header }}
                                </div>
                            </div>
                        </header>
                    @endisset

                    <!-- Page Content -->
                    <main class="flex-1">
                        {{ $slot }}
                    </main>
                </div>
            </div>

            <!-- Mobile header with sidebar toggle (simple) -->
            <div class="lg:hidden">
                <!-- Intentionally minimal for now; mobile nav can be added later -->
            </div>
        </div>

        <!-- Scripts Stack -->
        @stack('scripts')
    </body>
</html>
