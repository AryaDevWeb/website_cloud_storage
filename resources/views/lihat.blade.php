@extends('layouts.app')

@section('title', 'Lihat File')

@section('content')

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 mb-6 text-sm">
        <a href="/beranda/{{ auth()->id() }}" class="text-[#64748b] hover:text-[#2563eb] transition-colors">Beranda</a>
        <svg class="w-4 h-4 text-[#cbd5e1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-[#0f172a] font-medium truncate max-w-xs">{{ $file->nama_tampilan }}</span>
    </nav>

    {{-- File Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 bg-[#eff6ff] rounded-lg flex items-center justify-center text-[#2563eb] border border-[#bfdbfe]">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <h1 class="text-xl font-semibold text-[#0f172a] truncate max-w-sm">{{ $file->nama_tampilan }}</h1>
                <p class="text-sm text-[#64748b] mt-1">{{ $file->ukuran_format }} · Uploaded {{ $file->created_at->diffForHumans() }}</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="/download/{{ $file->id }}" class="px-4 py-2 bg-[#2563eb] hover:bg-[#1d4ed8] text-sm text-white font-medium rounded-lg transition-colors">
                Download
            </a>
            <a href="/hapus_file/{{ $file->id }}" class="px-4 py-2 bg-red-50 hover:bg-red-100 text-sm text-red-600 font-medium rounded-lg transition-colors" onclick="return confirm('Hapus file ini?')">
                Hapus
            </a>
        </div>
    </div>

    {{-- Content Card --}}
    <div class="bg-white border border-[#e2e8f0] rounded-2xl overflow-hidden shadow-sm transition-all duration-300">
        <div class="px-6 py-4 border-b border-[#e2e8f0] flex items-center justify-between bg-[#f8fafc]">
            <span class="text-xs font-bold text-[#64748b] uppercase tracking-wider">File Preview</span>
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold px-2 py-0.5 bg-blue-50 text-blue-600 rounded-md border border-blue-100 uppercase tracking-tighter">{{ $extension }}</span>
                @if($status === 'ready')
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                @endif
            </div>
        </div>

        <div class="min-h-[400px] flex items-center justify-center bg-gray-50/50 p-4 sm:p-8 relative">
            
            @if($status === 'processing')
                {{-- ──────── PROCESSING STATE ──────── --}}
                <div class="flex flex-col items-center gap-4 text-center py-12 animate-in fade-in duration-500">
                    <div class="relative w-16 h-16">
                        <svg class="animate-spin w-full h-full text-blue-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-10" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                            <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">Generating preview...</p>
                        <p class="text-xs text-gray-400 mt-1">We're optimizing your file for viewing.</p>
                    </div>
                </div>

            @elseif($status === 'failed')
                {{-- ──────── FAILED STATE ──────── --}}
                <div class="flex flex-col items-center gap-4 text-center py-12">
                    <div class="w-16 h-16 bg-red-50 rounded-2xl flex items-center justify-center text-red-500 border border-red-100">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">Preview generation failed</p>
                        <p class="text-xs text-gray-400 mt-1 mb-4">You can still download the original file.</p>
                        <a href="/download/{{ $file->id }}" class="inline-flex items-center gap-2 px-5 py-2 bg-gray-900 text-white text-xs font-bold rounded-xl hover:bg-black transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download Original
                        </a>
                    </div>
                </div>

            @else
                {{-- ──────── READY STATE ──────── --}}
                <div class="w-full h-full flex items-center justify-center">
                    
                    @if($preview_type === 'image')
                        <img src="/open_file_stream/{{ $file->id }}" 
                             alt="{{ $file->nama_tampilan }}" 
                             class="max-w-full max-h-[70vh] h-auto rounded-xl shadow-lg border border-white cursor-zoom-in transition-transform duration-300 hover:scale-[1.01]"
                             onclick="this.classList.toggle('max-h-none'); this.classList.toggle('cursor-zoom-in'); this.classList.toggle('cursor-zoom-out');">

                    @elseif($preview_type === 'video')
                        <div class="w-full max-w-4xl bg-black rounded-2xl overflow-hidden shadow-2xl border border-gray-100">
                            <video controls class="w-full max-h-[75vh]" preload="metadata">
                                <source src="/open_file_stream/{{ $file->id }}" type="video/{{ $extension === 'mov' ? 'mp4' : $extension }}">
                                Your browser does not support this video format.
                            </video>
                        </div>

                    @elseif($preview_type === 'audio')
                        <div class="w-full max-w-md bg-white p-10 rounded-3xl shadow-xl border border-gray-100/50 text-center flex flex-col items-center gap-6">
                            <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center text-blue-500 shadow-inner">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                            </div>
                            <div class="w-full">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Playing Audio</p>
                                <audio controls class="w-full">
                                    <source src="/open_file_stream/{{ $file->id }}" type="audio/{{ $extension }}">
                                </audio>
                            </div>
                        </div>

                    @elseif($preview_type === 'pdf')
                        <iframe src="/open_file_stream/{{ $file->id }}#toolbar=0" class="w-full h-[75vh] rounded-2xl border border-gray-200 shadow-sm bg-gray-100" frameborder="0"></iframe>

                    @elseif($preview_type === 'office')
                        @if($file->preview_path)
                            <iframe src="/open_file_stream/{{ $file->id }}?source=preview" class="w-full h-[75vh] rounded-2xl border border-gray-200 shadow-sm bg-gray-100" frameborder="0"></iframe>
                        @else
                            {{-- Office fallback --}}
                            <div class="flex flex-col items-center gap-4 text-center py-12">
                                <div class="w-20 h-20 bg-amber-50 rounded-3xl flex items-center justify-center text-amber-500 border border-amber-100">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">Office Preview Unavailable</p>
                                    <p class="text-xs text-gray-400 mt-1 mb-4">Offline viewing is not yet processed for this document.</p>
                                    <a href="/download/{{ $file->id }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700 transition-all shadow-md shadow-blue-200">
                                        Download to View
                                    </a>
                                </div>
                            </div>
                        @endif

                    @elseif($preview_type === 'text/code')
                        <div class="w-full h-[70vh] bg-[#0d1117] rounded-2xl border border-gray-800 shadow-2xl overflow-hidden flex flex-col">
                            <div class="px-4 py-2 bg-gray-900 border-b border-gray-800 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="flex gap-1.5">
                                        <div class="w-2.5 h-2.5 rounded-full bg-red-500/50"></div>
                                        <div class="w-2.5 h-2.5 rounded-full bg-amber-500/50"></div>
                                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500/50"></div>
                                    </div>
                                    <span class="ml-2 text-[10px] font-mono text-gray-500">{{ $file->nama_tampilan }}</span>
                                </div>
                            </div>
                            <div class="flex-1 overflow-auto custom-scrollbar">
                                <pre class="m-0 p-6 text-sm leading-relaxed"><code class="hljs language-{{ $extension }}" id="code-content">Loading source...</code></pre>
                            </div>
                        </div>
                        {{-- Load Highlight.js via CDN --}}
                        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                fetch('/open_file_stream/{{ $file->id }}')
                                    .then(res => res.text())
                                    .then(text => {
                                        const codeEl = document.getElementById('code-content');
                                        codeEl.textContent = text;
                                        if (typeof hljs !== 'undefined') {
                                            hljs.highlightElement(codeEl);
                                        }
                                    })
                                    .catch(err => {
                                        document.getElementById('code-content').textContent = 'Error loading file content.';
                                    });
                            });
                        </script>

                    @else
                        {{-- ──────── FALLBACK ──────── --}}
                        <div class="flex flex-col items-center gap-5 text-center py-16">
                            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center text-gray-300 border border-gray-200 shadow-inner">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-gray-900">Preview not available</h4>
                                <p class="text-xs text-gray-400 mt-1 max-w-[240px]">We don't support online viewing for .{{ $extension }} files yet.</p>
                                <div class="mt-6 flex flex-col gap-2">
                                    <a href="/download/{{ $file->id }}" 
                                       class="inline-flex items-center justify-center gap-2 px-8 py-3 bg-gray-900 text-white text-xs font-bold rounded-xl hover:bg-black transition-all shadow-lg hover:translate-y-[-2px] active:translate-y-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        Download File
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            @endif

        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 10px; height: 10px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #0d1117; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #30363d; border-radius: 5px; border: 2px solid #0d1117; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #484f58; }
    </style>


@endsection