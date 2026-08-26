<x-app-layout>
    @php
        $user = auth()->user();
        $quota = $user->storageQuota;
        $used = $quota?->used_bytes ?? 0;
        $max = $quota?->max_bytes ?? 1;
        $recentFiles = $user->media()->where('collection_name', 'files')->latest()->limit(5)->get();
        $fileCount = $user->media()->where('collection_name', 'files')->count();
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <!-- Hero Banner: Pure Dark / Gelap Polos with Sky Secondary Accents -->
        <div class="relative overflow-hidden rounded-3xl bg-zinc-950 border border-zinc-800/80 px-6 py-8 text-white shadow-xl sm:px-10 sm:py-10">
            <div class="relative z-10 max-w-2xl">
                <div class="flex items-center gap-2 mb-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-sky-500/10 text-sky-400 border border-sky-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-sky-400 animate-pulse"></span>
                        SMKN 1 Lumajang
                    </span>
                </div>
                <p class="text-xs font-bold uppercase tracking-wider text-sky-400">Selamat datang kembali</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight sm:text-4xl">Halo, {{ $user->name }}.</h1>
                <p class="mt-3 text-sm leading-6 text-zinc-300">Semua dokumen sekolah Anda tersimpan aman dalam satu ruang kerja yang rapi dan terorganisir.</p>
                <a href="{{ route('storage.index') }}" wire:navigate class="mt-6 inline-flex items-center justify-center rounded-xl bg-sky-500 hover:bg-sky-400 active:bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition">
                    Buka file manager <span class="ml-2">→</span>
                </a>
            </div>
            <!-- Background Orbs (Light Blue / Sky secondary) -->
            <div class="absolute -right-16 -top-24 h-72 w-72 rounded-full bg-sky-500/20 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-32 right-24 h-64 w-64 rounded-full bg-sky-400/15 blur-3xl pointer-events-none"></div>
        </div>

        <!-- Metric Cards: Putih Polos & Secondary Sky Accents -->
        <div class="mt-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-zinc-500">Penyimpanan terpakai</p>
                <p class="mt-2 text-2xl font-bold text-zinc-900">{{ number_format($used / 1048576, 1) }} MB</p>
                <div class="mt-4 h-2 rounded-full bg-zinc-100 overflow-hidden">
                    <div class="h-full rounded-full bg-sky-500 transition-all duration-500" style="width: {{ min(100, ($used / $max) * 100) }}%"></div>
                </div>
                <p class="mt-2 text-xs text-zinc-400">dari {{ number_format($max / 1048576, 0) }} MB</p>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-zinc-500">Total file</p>
                <p class="mt-2 text-2xl font-bold text-zinc-900">{{ $fileCount }}</p>
                <p class="mt-1 text-xs text-zinc-400">dokumen aktif</p>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-500">Aksi cepat</p>
                    <p class="mt-2 text-sm font-semibold text-zinc-900">Kelola ruang file</p>
                </div>
                <a href="{{ route('storage.index', ['section' => 'shared']) }}" wire:navigate class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 text-sky-600 hover:bg-sky-100 hover:text-sky-700 transition border border-sky-100">
                    ↗
                </a>
            </div>
        </div>

        <!-- Recent Files Section: Putih Polos & Sky Accents -->
        <div class="mt-8 rounded-2xl border border-zinc-200 bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b border-zinc-100 p-5">
                <div>
                    <h2 class="font-bold text-zinc-900">File terbaru</h2>
                    <p class="mt-0.5 text-sm text-zinc-500">Dokumen yang baru Anda tambahkan.</p>
                </div>
                <a href="{{ route('storage.index') }}" wire:navigate class="text-sm font-semibold text-sky-600 hover:text-sky-500 transition">
                    Lihat semua →
                </a>
            </div>
            <div class="divide-y divide-zinc-100">
                @forelse ($recentFiles as $file)
                    <div class="flex items-center gap-4 p-5 hover:bg-zinc-50/70 transition">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 border border-sky-100/80 text-[10px] font-bold text-sky-600">
                            {{ strtoupper(pathinfo($file->file_name, PATHINFO_EXTENSION)) ?: 'FILE' }}
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-zinc-800">{{ $file->file_name }}</p>
                            <p class="mt-0.5 text-xs text-zinc-400">{{ number_format($file->size / 1024, 1) }} KB · {{ $file->created_at?->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center text-sm text-zinc-400">
                        Belum ada file. <a href="{{ route('storage.index') }}" wire:navigate class="font-semibold text-sky-600 hover:text-sky-500">Upload sekarang.</a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
