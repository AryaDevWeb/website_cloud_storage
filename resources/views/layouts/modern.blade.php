<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaaS Storage - @yield('title', 'Modern Dashboard')</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .shadow-soft { box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.04), 0 6px 10px -6px rgba(0, 0, 0, 0.02); }
        .sidebar-active { background: #f1f5f9; color: #0f172a; border-right: 3px solid #6366f1; }
    </style>
</head>
<body class="bg-[#fcfcfd] text-[#1e293b]">

    {{-- SIDEBAR: SLEEK & FIXED --}}
    <aside class="fixed top-0 left-0 bottom-0 w-20 lg:w-64 bg-white border-r border-[#f1f5f9] z-50 flex flex-col transition-all">
        <div class="h-20 flex items-center px-6 lg:px-8 border-b border-[#f1f5f9]">
            <img src="{{ asset('images/CLD.png') }}" alt="CLD Logo" class="w-10 h-10 object-contain">
            <span class="ml-3 text-lg font-bold tracking-tight text-[#0f172a] hidden lg:block">CLD Storage</span>
        </div>

        <nav class="flex-1 py-6 px-3 lg:px-4 space-y-1">
            <a href="#" class="sidebar-active flex items-center justify-center lg:justify-start gap-4 px-4 py-3 rounded-xl font-medium transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="hidden lg:block text-sm">Dashboard</span>
            </a>
            <a href="#" class="flex items-center justify-center lg:justify-start gap-4 px-4 py-3 text-[#64748b] hover:bg-[#f8fafc] hover:text-[#0f172a] rounded-xl font-medium transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                <span class="hidden lg:block text-sm">Files</span>
            </a>
            <a href="/halaman_recent/{{ auth()->id() }}" class="flex items-center justify-center lg:justify-start gap-4 px-4 py-3 text-[#64748b] hover:bg-[#f8fafc] hover:text-[#0f172a] rounded-xl font-medium transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="hidden lg:block text-sm">Recent</span>
            </a>
            <a href="#" class="flex items-center justify-center lg:justify-start gap-4 px-4 py-3 text-[#64748b] hover:bg-[#f8fafc] hover:text-[#0f172a] rounded-xl font-medium transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="hidden lg:block text-sm">Shared</span>
            </a>
            <a href="/tempat_sampah" class="flex items-center justify-center lg:justify-start gap-4 px-4 py-3 text-[#64748b] hover:bg-[#f8fafc] hover:text-[#0f172a] rounded-xl font-medium transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span class="hidden lg:block text-sm">Trash</span>
            </a>
        </nav>

        {{-- STORAGE CARD --}}
        <div class="px-6 py-6 border-t border-[#f1f5f9] hidden lg:block">
            <div class="bg-indigo-600 rounded-2xl p-4 text-white">
                <p class="text-xs font-medium opacity-80 mb-2">Storage Usage</p>
                <p class="text-lg font-bold mb-3">56.4 GB <span class="text-xs font-normal opacity-70">of 100 GB</span></p>
                <div class="w-full bg-white/20 rounded-full h-1.5 mb-4 overflow-hidden">
                    <div class="bg-white h-full rounded-full" style="width: 56%"></div>
                </div>
                <button class="w-full py-2 bg-white/10 hover:bg-white/20 rounded-lg text-xs font-semibold transition-colors">Upgrade Plan</button>
            </div>
        </div>
    </aside>

    {{-- MAIN CONTENT AREA --}}
    <main class="ml-20 lg:ml-64 transition-all">
        {{-- TOP NAVBAR --}}
        <header class="sticky top-0 h-20 bg-white/80 backdrop-blur-md border-b border-[#f1f5f9] z-40 px-6 lg:px-8 flex items-center justify-between">
            <div class="flex-1 max-w-xl">
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-[#94a3b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" placeholder="Search for files, folders..." 
                        class="block w-full pl-12 pr-4 py-2.5 bg-[#f8fafc] border border-transparent rounded-2xl text-sm focus:bg-white focus:ring-4 focus:ring-indigo-100 transition-all outline-none">
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button class="hidden md:flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-2xl shadow-lg shadow-indigo-100 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"/>
                    </svg>
                    Upload File
                </button>
                <div class="w-10 h-10 rounded-2xl bg-slate-200 border-2 border-white shadow-sm overflow-hidden flex items-center justify-center text-slate-600 font-bold text-sm cursor-pointer">
                    JD
                </div>
            </div>
        </header>

        {{-- PAGE CONTENT --}}
        <div class="p-6 lg:p-8">
            @yield('content')
        </div>
    </main>

</body>
</html>
