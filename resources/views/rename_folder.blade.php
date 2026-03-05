@extends('layouts.app')

@section('title', isset($cari_folder) ? 'Rename: ' . $cari_folder->nama_folder : 'Rename Folder')

@section('content')

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 mb-6 text-sm">
        <a href="/beranda/{{ auth()->id() }}" class="text-[#94a3b8] hover:text-[#3b82f6] transition-colors">Beranda</a>
        <svg class="w-4 h-4 text-[#334155]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-white">Rename Folder</span>
    </div>

    <div class="max-w-lg">
        @if(isset($cari_folder))
        <div class="bg-[#111827] border border-[#1e293b] rounded-lg p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 bg-[#1e293b] rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-[#3b82f6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-sm font-medium text-white">Rename Folder</h1>
                    <p class="text-xs text-[#94a3b8]">{{ $cari_folder->nama_folder }}</p>
                </div>
            </div>

            @if(isset($pesan_berubah))
                <div class="mb-4 px-4 py-3 bg-green-500/10 border border-green-500/20 rounded-lg text-sm text-green-400">
                    {{ $pesan_berubah }}
                </div>
            @endif

            <form action="/rename_f/{{ $cari_folder->id }}" class="space-y-4">
                <div>
                    <label class="block text-sm text-[#94a3b8] mb-1.5">Nama baru</label>
                    <input name="rename" type="text" placeholder="{{ $cari_folder->nama_folder }}"
                        class="w-full px-3 py-2.5 bg-[#0a0f1e] border border-[#1e293b] rounded-lg text-sm text-[#e2e8f0] placeholder-[#475569] focus:outline-none focus:border-[#3b82f6] transition-colors">
                </div>
                <button class="w-full py-2.5 bg-[#3b82f6] hover:bg-[#2563eb] text-white text-sm font-medium rounded-lg transition-colors">
                    Rename
                </button>
            </form>
        </div>
        @endif
    </div>

@endsection