<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Pendaftaran Diterima — {{ config('app.name') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full font-sans antialiased text-zinc-900 bg-white dark:bg-zinc-950 dark:text-zinc-100">
        <div class="min-h-screen flex flex-col items-center justify-center py-12 px-4 text-center">

            <!-- School / FLASK Branding -->
            <div class="mb-6 text-center">
                <a href="/" class="inline-flex flex-col items-center gap-2 group">
                    <x-application-logo class="h-16 w-auto transition-transform duration-200 group-hover:scale-105" />
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-sky-600 dark:text-sky-400">
                            SMKN 1 Lumajang
                        </p>
                    </div>
                </a>
            </div>

            <!-- Card / Content -->
            <div class="w-full sm:max-w-md bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xl rounded-2xl p-8 flex flex-col items-center">
                <!-- Icon -->
                <div class="w-16 h-16 rounded-2xl bg-sky-50 dark:bg-sky-950/50 border border-sky-200 dark:border-sky-800/60 flex items-center justify-center mb-5">
                    <svg class="w-8 h-8 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>

                <h1 class="text-xl font-bold text-zinc-900 dark:text-white mb-2">
                    Pendaftaran Berhasil!
                </h1>

                <p class="text-sm text-zinc-600 dark:text-zinc-400 max-w-sm mb-2">
                    Akun Anda telah berhasil didaftarkan dan sedang menunggu persetujuan dari administrator sekolah.
                </p>

                <p class="text-xs text-zinc-400 dark:text-zinc-500 max-w-sm mb-6">
                    Anda akan mendapatkan notifikasi melalui email setelah akun diaktifkan.
                    Proses biasanya membutuhkan 1–2 hari kerja.
                </p>

                <a
                    href="{{ route('login') }}"
                    class="inline-flex items-center justify-center w-full px-5 py-2.5 text-sm font-semibold text-white dark:text-zinc-950 bg-sky-500 hover:bg-sky-600 dark:bg-sky-400 dark:hover:bg-sky-300 rounded-xl transition shadow-sm"
                >
                    Kembali ke Halaman Masuk
                </a>
            </div>

            <p class="mt-8 text-xs text-zinc-400 dark:text-zinc-600">
                SMKN 1 Lumajang — Cloud Storage & Platform Digital Sekolah
            </p>
        </div>
    </body>
</html>
