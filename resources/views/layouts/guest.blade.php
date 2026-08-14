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
    </head>
    <body class="h-full font-sans text-gray-900 antialiased bg-gray-50 dark:bg-gray-900">

        <div class="min-h-screen flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">

            <!-- School Branding -->
            <div class="mb-8 text-center">
                <a href="/" wire:navigate class="inline-flex flex-col items-center gap-2 group">
                    <x-application-logo class="w-16 h-16 fill-current text-indigo-600 dark:text-indigo-400 transition group-hover:opacity-80" />
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">
                            SMKN 1 Lumajang
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Platform Digital Sekolah
                        </p>
                    </div>
                </a>
            </div>

            <!-- Card -->
            <div class="w-full sm:max-w-md">
                <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl px-8 py-8">
                    {{ $slot }}
                </div>
            </div>

        </div>

    </body>
</html>
