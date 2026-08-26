<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' — ' : '' }}{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts / Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script>
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>
    </head>
    <body class="h-full font-sans antialiased text-zinc-900 bg-white dark:bg-zinc-950 dark:text-zinc-100">

        <div class="relative min-h-screen flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <!-- Theme Toggle Top Right -->
            <div class="absolute top-5 right-5">
                <x-theme-toggle class="bg-white/80 dark:bg-zinc-900/80 border border-zinc-200 dark:border-zinc-800 shadow-sm" />
            </div>

            <!-- School / FLASK Branding -->
            <div class="mb-8 text-center">
                <a href="/" wire:navigate class="inline-flex flex-col items-center gap-2 group">
                    <x-application-logo class="h-20 w-auto transition-transform duration-200 group-hover:scale-105" />
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-sky-600 dark:text-sky-400">
                            SMKN 1 Lumajang
                        </p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                            Cloud Storage & Platform Digital Sekolah
                        </p>
                    </div>
                </a>
            </div>

            <!-- Card -->
            <div class="w-full sm:max-w-md">
                <div class="bg-white dark:bg-zinc-900 shadow-xl border border-zinc-200 dark:border-zinc-800 rounded-2xl px-8 py-8">
                    {{ $slot }}
                </div>
            </div>

        </div>

    </body>
</html>
