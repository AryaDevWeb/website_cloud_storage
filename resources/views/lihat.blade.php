@extends('layouts.app')

@section('title', isset($file) ? $file->file : 'Lihat File')

@section('content')

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 mb-6 text-sm">
        <a href="/beranda/{{ auth()->id() }}" class="text-[#94a3b8] hover:text-[#3b82f6] transition-colors">Beranda</a>
        <svg class="w-4 h-4 text-[#334155]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-white truncate">{{ $file->file }}</span>
    </div>

    {{-- File Content Card --}}
    <div class="bg-[#111827] border border-[#1e293b] rounded-lg">
        <div class="flex items-center justify-between px-5 py-3 border-b border-[#1e293b]">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-[#94a3b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <h1 class="text-sm font-medium text-white">{{ $file->file }}</h1>
            </div>
        </div>
        <div class="p-5">
            <div class="bg-[#0a0f1e] border border-[#1e293b] rounded-lg p-4 text-sm text-[#e2e8f0] leading-relaxed font-mono whitespace-pre-wrap break-words">
                {!! nl2br(e($teks)) !!}
            </div>
        </div>
    </div>

@endsection