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
<body class="bg-[#f8fafc] min-h-screen font-[Inter] text-[#0f172a]">

        {{-- Drag & Drop Overlay --}}
        <div id="drop-overlay" class="fixed inset-0 z-50 bg-[#2563eb]/10 border-4 border-dashed border-[#2563eb] flex items-center justify-center pointer-events-none opacity-0 transition-opacity duration-200">
            <div class="bg-white px-8 py-6 rounded-lg text-center border border-[#e2e8f0]">
                <div class="w-16 h-16 bg-[#eff6ff] rounded-lg flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-[#2563eb]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-[#0f172a] mb-1">Lepas untuk Upload</h2>
                <p class="text-sm text-[#64748b]">File Anda akan langsung disimpan</p>
            </div>
        </div>

        {{-- Sidebar --}}
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-30 w-64 bg-white border-r border-[#e2e8f0] flex flex-col transition-transform duration-200 -translate-x-full lg:translate-x-0">

            {{-- Brand --}}
            <div class="flex items-center justify-center py-8 border-b border-[#e2e8f0]">
                <a href="/">
                    <img src="{{ asset('images/CLD.png') }}" alt="CLD Logo" class="w-20 h-20 object-contain">
                </a>
            </div>

            {{-- User Info & Storage --}}
            @auth
            <div class="px-5 py-5 border-b border-[#e2e8f0] space-y-4">
                <a href="/lihat_akun/{{ auth()->id() }}" class="flex items-center gap-3 group">
                    <div class="w-9 h-9 bg-[#2563eb] rounded-lg flex items-center justify-center text-white text-sm font-semibold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-[#0f172a] truncate group-hover:text-[#2563eb] transition-colors">{{ auth()->user()->name }}</p>
                    </div>
                </a>

                {{-- Storage Indicator --}}
                <div class="space-y-2">
                    <div class="flex justify-between text-xs text-[#64748b]">
                        <span>Penyimpanan</span>
                        <span class="font-medium">{{ number_format(auth()->user()->storage_used / 1024 / 1024, 1) }}MB / {{ number_format(auth()->user()->storage_quota / 1024 / 1024, 0) }}MB</span>
                    </div>
                    <div class="h-1.5 w-full bg-[#e2e8f0] rounded-full overflow-hidden">
                        @php
                            $percentage = (auth()->user()->storage_used / auth()->user()->storage_quota) * 100;
                        @endphp
                        <div class="h-full bg-[#2563eb] rounded-full" style="width: {{ min($percentage, 100) }}%"></div>
                    </div>
                </div>
            </div>
            @endauth

            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <a href="/beranda/{{ auth()->id() }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ Request::is('beranda*') ? 'bg-[#eff6ff] text-[#2563eb]' : 'text-[#64748b] hover:text-[#0f172a] hover:bg-[#f1f5f9]' }} transition-colors font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1m-2 0h2"/>
                    </svg>
                    Beranda
                </a>
                <a href="/lihat_akun/{{ auth()->id() }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ Request::is('lihat_akun*') ? 'bg-[#eff6ff] text-[#2563eb]' : 'text-[#64748b] hover:text-[#0f172a] hover:bg-[#f1f5f9]' }} transition-colors font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Akun Saya
                </a>
            </nav>

            @auth
            <div class="px-3 py-4 border-t border-[#e2e8f0]">
                <form action="/logout" method="POST">
                    @csrf
                    <button type="submit" class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm text-[#64748b] hover:text-red-600 hover:bg-red-50 transition-colors font-medium">
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
        <div class="fixed top-0 left-0 right-0 z-20 flex items-center justify-between bg-white border-b border-[#e2e8f0] px-4 py-3 lg:hidden">
            <button onclick="document.getElementById('sidebar').classList.toggle('-translate-x-full')" class="text-[#64748b] hover:text-[#0f172a] p-1 hover:bg-[#f1f5f9] rounded-lg transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <div class="flex items-center">
                <img src="{{ asset('images/logo CLD.svg') }}" alt="CLD Logo" class="w-9 h-9">
            </div>
            <div class="w-8"></div>
        </div>

        {{-- Mobile FAB for Upload --}}
        <div class="fixed bottom-6 right-6 z-40 lg:hidden">
            <form id="fab-upload-form" action="/upload" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="folder_id" value="{{ $isi_folder->id ?? '' }}">
                <label class="flex items-center justify-center w-14 h-14 bg-[#2563eb] text-white rounded-full cursor-pointer shadow-md">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <input type="file" name="upload" class="hidden" onchange="this.form.submit()">
                </label>
            </form>
        </div>

        {{-- Overlay for mobile sidebar --}}
        <div id="sidebar-overlay" class="fixed inset-0 z-20 bg-black/30 hidden lg:hidden" onclick="document.getElementById('sidebar').classList.add('-translate-x-full'); this.classList.add('hidden')"></div>

        {{-- Main Content --}}
        <main class="flex-1 lg:ml-64 pt-14 lg:pt-0">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-8">

                {{-- Flash Messages --}}
                @if(session('error'))
                <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600 flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('error') }}
                </div>
                @endif

                @if(session('status') || session('notif') || session('nama_tampil') || session('folder_status') || session('status_file'))
                <div class="mb-4 px-4 py-3 bg-[#eff6ff] border border-[#bfdbfe] rounded-lg text-sm text-[#2563eb] flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('status') ?? session('notif') ?? (session('nama_tampil') ? 'File ' . session('nama_tampil') . ' berhasil ditambahkan!' : '') ?? session('folder_status') ?? session('status_file') }}
                </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script>
        const dropOverlay = document.getElementById('drop-overlay');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        
        // Mobile Sidebar Observer
        const observer = new MutationObserver(() => {
            if (!sidebar.classList.contains('-translate-x-full')) {
                overlay.classList.remove('hidden');
            } else {
                overlay.classList.add('hidden');
            }
        });
        observer.observe(sidebar, { attributes: true, attributeFilter: ['class'] });

        // Global Drag & Drop Handler (Only for desktop)
        if (window.innerWidth >= 1024) {
            window.addEventListener('dragenter', (e) => {
                e.preventDefault();
                dropOverlay.classList.remove('opacity-0', 'pointer-events-none');
            });

            dropOverlay.addEventListener('dragleave', (e) => {
                if (e.relatedTarget === null || !dropOverlay.contains(e.relatedTarget)) {
                    dropOverlay.classList.add('opacity-0', 'pointer-events-none');
                }
            });

            window.addEventListener('dragover', (e) => {
                e.preventDefault();
            });

            window.addEventListener('drop', (e) => {
                e.preventDefault();
                dropOverlay.classList.add('opacity-0', 'pointer-events-none');
                
                if (e.dataTransfer.files.length > 0) {
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('upload', e.dataTransfer.files[0]);
                    
                    const folderIdInput = document.querySelector('input[name="folder_id"]');
                    if (folderIdInput) {
                        formData.append('folder_id', folderIdInput.value);
                    }

                    fetch('/upload', {
                        method: 'POST',
                        body: formData
                    }).then(() => {
                        window.location.reload();
                    }).catch(error => {
                        console.error('Upload failed:', error);
                        alert('Gagal upload file');
                    });
                }
            });
        }
    </script>

</body>
</html>
