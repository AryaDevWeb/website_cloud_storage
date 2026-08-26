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
    <body class="min-h-full font-sans antialiased text-zinc-900 bg-zinc-50 dark:bg-zinc-950 dark:text-zinc-100">
        <div x-data="{ sidebarOpen: true, mobileSidebarOpen: false }" class="min-h-screen flex flex-col">
            <livewire:layout.navigation />

            <!-- Main Page Content Area with dynamic margin based on sidebar state -->
            <div
                :class="sidebarOpen ? 'md:pl-64' : 'md:pl-0'"
                class="flex flex-col flex-1 min-w-0 transition-all duration-300 ease-in-out"
            >
                @if (isset($header))
                    <header class="border-b border-zinc-200 bg-white/80 dark:bg-zinc-900/80 dark:border-zinc-800 backdrop-blur">
                        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
