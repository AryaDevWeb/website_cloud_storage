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
<body class="bg-[#f8fafc] min-h-screen flex items-center justify-center font-[Inter] text-[#0f172a] p-4">

    <div class="w-full max-w-md">
        {{-- Brand --}}
        {{-- Logo --}}
        <div class="text-center mb-10">
            <img src="{{ asset('images/CLD.png') }}" alt="CLD Logo" class="w-20 h-20 mx-auto object-contain" style="max-width: 80px; max-height: 80px;">
        </div>

        {{-- Card --}}
        <div class="bg-white border border-[#e2e8f0] rounded-lg p-6">
            @yield('content')
        </div>
    </div>

</body>
</html>
