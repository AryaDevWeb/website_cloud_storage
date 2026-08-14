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
    <body class="h-full font-sans antialiased bg-gray-50 dark:bg-gray-900">
        <div class="min-h-screen flex flex-col items-center justify-center py-12 px-4 text-center">

            <!-- Icon -->
            <div class="w-20 h-20 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center mb-6">
                <svg class="w-10 h-10 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>

            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">
                Pendaftaran Berhasil!
            </h1>

            <p class="text-gray-500 dark:text-gray-400 max-w-sm mb-2">
                Akun Anda telah berhasil didaftarkan dan sedang menunggu persetujuan dari administrator sekolah.
            </p>

            <p class="text-sm text-gray-400 dark:text-gray-500 max-w-sm mb-8">
                Anda akan mendapatkan notifikasi melalui email setelah akun diaktifkan.
                Proses biasanya membutuhkan 1–2 hari kerja.
            </p>

            <a
                href="{{ route('login') }}"
                class="inline-flex items-center px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors"
            >
                Kembali ke Halaman Masuk
            </a>

            <p class="mt-6 text-xs text-gray-400 dark:text-gray-600">
                SMKN 1 Lumajang — Platform Digital Sekolah
            </p>
        </div>
    </body>
</html>
