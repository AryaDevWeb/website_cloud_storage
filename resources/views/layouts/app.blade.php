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
<body class="bg-[#0a0f1e] min-h-screen font-[Inter] text-[#e2e8f0]">

    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-30 w-64 bg-[#111827] border-r border-[#1e293b] flex flex-col transition-transform duration-200 -translate-x-full lg:translate-x-0">

            {{-- Brand --}}
            <div class="flex items-center gap-3 px-5 py-5 border-b border-[#1e293b]">
                <div class="flex items-center justify-center w-9 h-9 bg-[#0a0f1e] border border-[#1e293b] rounded-lg">
                    <svg class="w-5 h-5 text-[#3b82f6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
                    </svg>
                </div>
                <span class="text-base font-semibold text-white">Cloud Storage</span>
            </div>

            {{-- User Info --}}
            @auth
            <div class="px-5 py-4 border-b border-[#1e293b]">
                <a href="/lihat_akun/{{ auth()->id() }}" class="flex items-center gap-3 group">
                    <div class="w-9 h-9 bg-[#3b82f6] rounded-lg flex items-center justify-center text-white text-sm font-semibold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-white truncate group-hover:text-[#3b82f6] transition-colors">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-[#94a3b8]">
                            Koin: {{ auth()->user()->wallets->koin ?? 0 }}
                        </p>
                    </div>
                </a>
            </div>
            @endauth

            {{-- Navigation --}}
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <a href="/beranda/{{ auth()->id() }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-[#94a3b8] hover:text-white hover:bg-[#1e293b] transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1m-2 0h2"/>
                    </svg>
                    Beranda
                </a>
                <a href="/lihat_akun/{{ auth()->id() }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-[#94a3b8] hover:text-white hover:bg-[#1e293b] transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Akun Saya
                </a>
            </nav>

            {{-- Logout --}}
            @auth
            <div class="px-3 py-4 border-t border-[#1e293b]">
                <form action="/logout" method="POST">
                    @csrf
                    <button type="submit" class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm text-[#94a3b8] hover:text-red-400 hover:bg-[#1e293b] transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Keluar
                    </button>
                </form>
            </div>
            @endauth
        </aside>

        {{-- Mobile Header --}}
        <div class="fixed top-0 left-0 right-0 z-20 flex items-center justify-between bg-[#111827] border-b border-[#1e293b] px-4 py-3 lg:hidden">
            <button onclick="document.getElementById('sidebar').classList.toggle('-translate-x-full')" class="text-[#94a3b8] hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <span class="text-sm font-semibold text-white">Cloud Storage</span>
            <div class="w-6"></div>
        </div>

        {{-- Overlay for mobile sidebar --}}
        <div id="sidebar-overlay" class="fixed inset-0 z-20 bg-black/50 hidden lg:hidden" onclick="document.getElementById('sidebar').classList.add('-translate-x-full'); this.classList.add('hidden')"></div>

        {{-- Main Content --}}
        <main class="flex-1 lg:ml-64 pt-14 lg:pt-0">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-8">

                {{-- Flash Messages --}}
                @if(session('error'))
                <div class="mb-4 px-4 py-3 bg-red-500/10 border border-red-500/20 rounded-lg text-sm text-red-400">
                    {{ session('error') }}
                </div>
                @endif

                @if(session('status'))
                <div class="mb-4 px-4 py-3 bg-[#3b82f6]/10 border border-[#3b82f6]/20 rounded-lg text-sm text-[#3b82f6]">
                    {{ session('status') }}
                </div>
                @endif

                @if(session('notif'))
                <div class="mb-4 px-4 py-3 bg-green-500/10 border border-green-500/20 rounded-lg text-sm text-green-400">
                    {{ session('notif') }}
                </div>
                @endif

                @if(session('hapus_sukses'))
                <div class="mb-4 px-4 py-3 bg-green-500/10 border border-green-500/20 rounded-lg text-sm text-green-400">
                    {{ session('hapus_sukses') }}
                </div>
                @endif

                @if(session('nama_tampil'))
                <div class="mb-4 px-4 py-3 bg-green-500/10 border border-green-500/20 rounded-lg text-sm text-green-400">
                    File {{ session('nama_tampil') }} berhasil ditambahkan!
                </div>
                @endif

                @if(session('folder_status'))
                <div class="mb-4 px-4 py-3 bg-green-500/10 border border-green-500/20 rounded-lg text-sm text-green-400">
                    {{ session('folder_status') }}
                </div>
                @endif

                @if(session('status_subfolder'))
                <div class="mb-4 px-4 py-3 bg-green-500/10 border border-green-500/20 rounded-lg text-sm text-green-400">
                    {{ session('status_subfolder') }}
                </div>
                @endif

                @if(session('status_file'))
                <div class="mb-4 px-4 py-3 bg-green-500/10 border border-green-500/20 rounded-lg text-sm text-green-400">
                    {{ session('status_file') }}
                </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script>
        // Toggle mobile sidebar overlay
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const observer = new MutationObserver(() => {
            if (!sidebar.classList.contains('-translate-x-full')) {
                overlay.classList.remove('hidden');
            } else {
                overlay.classList.add('hidden');
            }
        });
        observer.observe(sidebar, { attributes: true, attributeFilter: ['class'] });
    </script>

</body>
</html>
