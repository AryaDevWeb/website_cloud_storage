@extends('layouts.app')

@section('title', 'Beranda')

@section('content')

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-semibold text-white">Beranda</h1>
            <p class="text-sm text-[#94a3b8] mt-1">Kelola file dan folder Anda</p>
        </div>

        {{-- Search --}}
        <form action="/pencarian" class="flex gap-2">
            <input name="cari" placeholder="Cari file atau folder..." type="text"
                class="px-3 py-2 bg-[#0a0f1e] border border-[#1e293b] rounded-lg text-sm text-[#e2e8f0] placeholder-[#475569] focus:outline-none focus:border-[#3b82f6] transition-colors w-full sm:w-64">
            <button class="px-4 py-2 bg-[#1e293b] hover:bg-[#334155] text-sm text-[#e2e8f0] rounded-lg transition-colors flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>
        </form>
    </div>

    {{-- Search Results --}}
    @if (isset($folders) && isset($files) && ($folders->count() || $files->count()))
    <div class="mb-6">
        <h2 class="text-sm font-medium text-[#94a3b8] mb-3">Hasil Pencarian</h2>
        <div class="space-y-2">
            @foreach ($folders as $isi_folder)
                <div class="flex items-center justify-between px-4 py-3 bg-[#111827] border border-[#1e293b] rounded-lg">
                    <span class="text-sm text-[#e2e8f0]">📁 {{ $isi_folder->nama_folder }}</span>
                    <a href="/hapus_folder/{{ $isi_folder->id }}" class="text-xs text-red-400 hover:text-red-300 transition-colors">Hapus</a>
                </div>
            @endforeach

            @foreach ($files as $isi_file)
                <div class="flex items-center justify-between px-4 py-3 bg-[#111827] border border-[#1e293b] rounded-lg">
                    <span class="text-sm text-[#e2e8f0]">{{ $isi_file->nama_tampilan }}</span>
                    <a href="/hapus/{{ $isi_file->id }}" class="text-xs text-red-400 hover:text-red-300 transition-colors">Hapus</a>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    @if(isset($folders) && isset($files) && $files->isEmpty() && $folders->isEmpty())
        <div class="mb-6 px-4 py-3 bg-[#111827] border border-[#1e293b] rounded-lg text-sm text-[#94a3b8] text-center">
            Data tidak ditemukan
        </div>
    @endif

    @if (isset($status_rename))
        <div class="mb-4 px-4 py-3 bg-green-500/10 border border-green-500/20 rounded-lg text-sm text-green-400">
            {{ $status_rename }}
        </div>
    @endif

    {{-- Actions Bar --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
        {{-- Upload File --}}
        <div class="bg-[#111827] border border-[#1e293b] rounded-lg p-4">
            <h3 class="text-sm font-medium text-white mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-[#3b82f6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                Upload File
            </h3>
            <form action="/upload" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3">
                @csrf
                <input name="upload" type="file" class="text-sm">
                <button class="w-full py-2 bg-[#3b82f6] hover:bg-[#2563eb] text-white text-sm font-medium rounded-lg transition-colors">
                    Upload
                </button>
            </form>
        </div>

        {{-- Create Folder --}}
        <div class="bg-[#111827] border border-[#1e293b] rounded-lg p-4">
            <h3 class="text-sm font-medium text-white mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-[#3b82f6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                </svg>
                Buat Folder
            </h3>
            <form action="/folder" method="POST" class="flex gap-2">
                @csrf
                <input placeholder="Nama folder..." name="nama" type="text"
                    class="flex-1 px-3 py-2 bg-[#0a0f1e] border border-[#1e293b] rounded-lg text-sm text-[#e2e8f0] placeholder-[#475569] focus:outline-none focus:border-[#3b82f6] transition-colors">
                <input name="parent_id" value="" type="hidden">
                <button class="px-4 py-2 bg-[#1e293b] hover:bg-[#334155] text-sm text-[#e2e8f0] rounded-lg transition-colors flex-shrink-0">
                    Buat
                </button>
            </form>
        </div>
    </div>

    {{-- Folders Section --}}
    @if(auth()->user()->folders->where('parent_id', null)->count())
    <div class="mb-8">
        <h2 class="text-sm font-medium text-[#94a3b8] uppercase tracking-wider mb-3">Folder</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach (auth()->user()->folders->where('parent_id', null) as $newFolder)
            <div class="bg-[#111827] border border-[#1e293b] rounded-lg p-4 hover:border-[#334155] transition-colors">
                <a href="/folder_open/{{ $newFolder->id }}" class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-[#1e293b] rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-[#3b82f6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-white truncate hover:text-[#3b82f6] transition-colors">{{ $newFolder->nama_folder }}</p>
                        <p class="text-xs text-[#94a3b8]">{{ $newFolder->created_at->format('d-m-Y') }}</p>
                    </div>
                </a>
                <div class="flex items-center gap-2 pt-3 border-t border-[#1e293b]">
                    <a href="/masuk_izin/{{ $newFolder->id }}" class="text-xs text-[#94a3b8] hover:text-[#3b82f6] transition-colors">Perizinan</a>
                    <span class="text-[#1e293b]">·</span>
                    <a href="/rename_folder/{{ $newFolder->id }}" class="text-xs text-[#94a3b8] hover:text-[#3b82f6] transition-colors">Rename</a>
                    <span class="text-[#1e293b]">·</span>
                    <a href="/hapus_folder/{{ $newFolder->id }}" class="text-xs text-[#94a3b8] hover:text-red-400 transition-colors">Hapus</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Files Section --}}
    @if(auth()->user()->galleries->count())
    <div>
        <h2 class="text-sm font-medium text-[#94a3b8] uppercase tracking-wider mb-3">File</h2>
        <div class="bg-[#111827] border border-[#1e293b] rounded-lg divide-y divide-[#1e293b]">
            @foreach (auth()->user()->galleries as $hasil_file)
            <div class="flex items-center justify-between px-4 py-3 hover:bg-[#1e293b]/50 transition-colors">
                <a href="/open_file/{{ $hasil_file->id }}" class="flex items-center gap-3 min-w-0 flex-1">
                    <div class="w-9 h-9 bg-[#1e293b] rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-[#94a3b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm text-white truncate hover:text-[#3b82f6] transition-colors">{{ $hasil_file->nama_tampilan }}</p>
                        <p class="text-xs text-[#94a3b8]">{{ $hasil_file->ukuran_format }} · {{ $hasil_file->created_at->format('d-m-Y') }}</p>
                    </div>
                </a>
                <div class="flex items-center gap-3 ml-4 flex-shrink-0">
                    <a href="/rename_file/{{ $hasil_file->id }}" class="text-xs text-[#94a3b8] hover:text-[#3b82f6] transition-colors" title="Rename">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </a>
                    <a href="/izin_file/{{ $hasil_file->id }}" class="text-xs text-[#94a3b8] hover:text-[#3b82f6] transition-colors" title="Perizinan">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </a>
                    <a href="/download/{{ $hasil_file->id }}" class="text-xs text-[#94a3b8] hover:text-green-400 transition-colors" title="Download">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </a>
                    <a href="/hapus/{{ $hasil_file->id }}" class="text-xs text-[#94a3b8] hover:text-red-400 transition-colors" title="Hapus">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Empty State --}}
    @if(auth()->user()->galleries->count() == 0 && auth()->user()->folders->where('parent_id', null)->count() == 0)
    <div class="text-center py-16">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-[#111827] border border-[#1e293b] rounded-lg mb-4">
            <svg class="w-8 h-8 text-[#94a3b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
        </div>
        <p class="text-sm text-[#94a3b8]">Belum ada file atau folder. Upload file atau buat folder untuk memulai.</p>
    </div>
    @endif

@endsection