<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Cloud Storage')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0a0f1e] min-h-screen flex items-center justify-center font-[Inter] text-[#e2e8f0] p-4">

    <div class="w-full max-w-md">
        {{-- Logo / Brand --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-[#111827] border border-[#1e293b] rounded-lg mb-4">
                <svg class="w-7 h-7 text-[#3b82f6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
                </svg>
            </div>
            <h1 class="text-xl font-semibold text-white">Cloud Storage</h1>
            <p class="text-sm text-[#94a3b8] mt-1">Simpan file Anda dengan aman</p>
        </div>

        {{-- Card --}}
        <div class="bg-[#111827] border border-[#1e293b] rounded-lg p-6">
            @yield('content')
        </div>
    </div>

</body>
</html>
