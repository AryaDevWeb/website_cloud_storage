@extends('layouts.app')

@section('title', 'Perizinan File')

@section('content')

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 mb-6 text-sm">
        <a href="/beranda/{{ auth()->id() }}" class="text-[#94a3b8] hover:text-[#3b82f6] transition-colors">Beranda</a>
        <svg class="w-4 h-4 text-[#334155]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-white">Perizinan File</span>
    </div>

    <div class="max-w-lg">
        <div class="bg-[#111827] border border-[#1e293b] rounded-lg p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 bg-[#1e293b] rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-[#94a3b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-sm font-medium text-white">{{ $isi_file->file }}</h1>
                    <p class="text-xs text-[#94a3b8]">Status: <span class="{{ $isi_file->izin == 1 ? 'text-green-400' : 'text-yellow-400' }}">{{ $isi_file->izin == 1 ? 'Public' : 'Private' }}</span></p>
                </div>
            </div>

            <form action="/ubah_perizinan/{{ $isi_file->id }}" method="POST">
                @csrf
                <div class="space-y-3 mb-5">
                    <label class="flex items-center gap-3 px-4 py-3 bg-[#0a0f1e] border border-[#1e293b] rounded-lg cursor-pointer hover:border-[#334155] transition-colors">
                        <input type="radio" name="izin" value="0" {{ $isi_file->izin == 0 ? 'checked' : '' }}>
                        <div>
                            <p class="text-sm text-white">Private</p>
                            <p class="text-xs text-[#94a3b8]">Hanya Anda yang bisa mengakses</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 px-4 py-3 bg-[#0a0f1e] border border-[#1e293b] rounded-lg cursor-pointer hover:border-[#334155] transition-colors">
                        <input type="radio" name="izin" value="1" {{ $isi_file->izin == 1 ? 'checked' : '' }}>
                        <div>
                            <p class="text-sm text-white">Public</p>
                            <p class="text-xs text-[#94a3b8]">Semua orang bisa mengakses</p>
                        </div>
                    </label>
                </div>
                <button type="submit" class="w-full py-2.5 bg-[#3b82f6] hover:bg-[#2563eb] text-white text-sm font-medium rounded-lg transition-colors">
                    Ubah Perizinan
                </button>
            </form>

            @if (session('status'))
                <div class="mt-4 px-4 py-3 bg-green-500/10 border border-green-500/20 rounded-lg text-sm text-green-400">
                    {{ session('status') }}
                </div>
            @endif
        </div>
    </div>

@endsection