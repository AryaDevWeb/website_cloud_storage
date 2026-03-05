@extends('layouts.app')

@section('title', isset($lihat_akun) ? $lihat_akun->name : 'Akun')

@section('content')

    <div class="max-w-lg">
        {{-- Page Header --}}
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-white">Akun Saya</h1>
            <p class="text-sm text-[#94a3b8] mt-1">Kelola profil Anda</p>
        </div>

        {{-- User Card --}}
        @if(isset($lihat_akun))
        <div class="bg-[#111827] border border-[#1e293b] rounded-lg p-6 mb-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 bg-[#3b82f6] rounded-lg flex items-center justify-center text-white text-xl font-semibold">
                    {{ strtoupper(substr($lihat_akun->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-white">{{ $lihat_akun->name }}</h2>
                    <p class="text-sm text-[#94a3b8]">Anggota</p>
                </div>
            </div>

            <div class="space-y-3 pt-4 border-t border-[#1e293b]">
                <a href="/beranda/{{ auth()->id() }}"
                    class="flex items-center gap-3 w-full px-4 py-2.5 bg-[#1e293b] hover:bg-[#334155] rounded-lg text-sm text-[#e2e8f0] transition-colors">
                    <svg class="w-4 h-4 text-[#94a3b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1m-2 0h2"/>
                    </svg>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
        @endif

        {{-- Danger Zone --}}
        <div class="bg-[#111827] border border-red-500/20 rounded-lg p-6">
            <h3 class="text-sm font-medium text-red-400 mb-1">Zona Berbahaya</h3>
            <p class="text-xs text-[#94a3b8] mb-4">Tindakan ini tidak dapat dibatalkan.</p>
            <div class="space-y-2">
                <a href="/hapus_akun/{{ auth()->id() }}"
                    class="flex items-center justify-center gap-2 w-full px-4 py-2.5 bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 rounded-lg text-sm text-red-400 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus Akun
                </a>
                <form action="/logout" method="POST">
                    @csrf
                    <button type="submit"
                        class="flex items-center justify-center gap-2 w-full px-4 py-2.5 bg-[#1e293b] hover:bg-[#334155] rounded-lg text-sm text-[#e2e8f0] transition-colors">
                        <svg class="w-4 h-4 text-[#94a3b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>

@endsection