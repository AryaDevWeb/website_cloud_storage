@extends('layouts.app')

@section('title', $isi_folder->nama_folder)

@section('content')

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 mb-6 text-sm">
        <a href="/beranda/{{ auth()->id() }}" class="text-[#64748b] hover:text-[#2563eb] transition-colors flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
            File Saya
        </a>
        <svg class="w-4 h-4 text-[#cbd5e1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-[#0f172a] font-medium">{{ $isi_folder->nama_folder }}</span>
    </nav>

    {{-- Folder Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-[#fffbeb] rounded-xl flex items-center justify-center text-[#f59e0b] border border-[#fef3c7]">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <h1 class="text-xl font-semibold text-[#0f172a] truncate max-w-sm">{{ $isi_folder->nama_folder }}</h1>
                <p class="text-sm text-[#64748b] mt-1">{{ $isi_folder->files->count() }} file · Dibuat {{ $isi_folder->created_at->format('d M Y') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            {{-- View Toggle --}}
            <div class="flex items-center bg-white border border-[#e2e8f0] rounded-xl p-1">
                <button onclick="switchView('grid')" id="grid-btn" class="view-toggle-btn active" title="Grid View">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </button>
                <button onclick="switchView('list')" id="list-btn" class="view-toggle-btn" title="List View">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </button>
            </div>

            <div class="h-8 w-px bg-[#e2e8f0]"></div>

            {{-- Folder Actions --}}
            <a href="/rename_folder/{{ $isi_folder->id }}" class="p-2 text-[#94a3b8] hover:text-[#2563eb] hover:bg-[#eff6ff] rounded-xl" title="Rename">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </a>
            <a href="/permissions_folder/{{ $isi_folder->id }}" class="p-2 text-[#94a3b8] hover:text-[#2563eb] hover:bg-[#eff6ff] rounded-xl" title="Izin">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </a>
            <a href="/hapus_folder/{{ $isi_folder->id }}" class="p-2 text-[#94a3b8] hover:text-red-500 hover:bg-red-50 rounded-xl" title="Hapus" onclick="return confirm('Hapus folder ini?')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </a>
        </div>
    </div>

    {{-- Subfolders --}}
    @if($isi_folder->children->count())
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-4">
            <svg class="w-4 h-4 text-[#f59e0b]" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
            </svg>
            <h2 class="text-xs font-semibold text-[#64748b] uppercase tracking-wider">Subfolder</h2>
            <span class="text-xs text-[#94a3b8] bg-[#f1f5f9] px-2 py-0.5 rounded-full">{{ $isi_folder->children->count() }}</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
            @foreach ($isi_folder->children as $sub)
            <div class="group file-card bg-white border border-[#e2e8f0] rounded-xl p-4">
                <div class="flex items-start justify-between mb-3">
                    <a href="{{ route('folder.show', $sub->id) }}" class="flex items-center gap-3 min-w-0 flex-1">
                        <div class="w-10 h-10 bg-[#fffbeb] rounded-xl flex items-center justify-center text-[#f59e0b] shrink-0">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-[#0f172a] truncate group-hover:text-[#2563eb]">{{ $sub->nama_folder }}</p>
                            <p class="text-xs text-[#94a3b8]">{{ $sub->created_at->format('d M Y') }}</p>
                        </div>
                    </a>
                </div>
                <div class="flex items-center justify-end gap-1 pt-3 border-t border-[#f1f5f9]">
                    <a href="/rename_folder/{{ $sub->id }}" class="p-1.5 text-[#94a3b8] hover:text-[#2563eb] hover:bg-[#eff6ff] rounded-lg" title="Rename">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </a>
                    <a href="/permissions_folder/{{ $sub->id }}" class="p-1.5 text-[#94a3b8] hover:text-[#2563eb] hover:bg-[#eff6ff] rounded-lg" title="Izin">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </a>
                    <a href="/hapus_folder/{{ $sub->id }}" class="p-1.5 text-[#94a3b8] hover:text-red-500 hover:bg-red-50 rounded-lg" title="Hapus" onclick="return confirm('Hapus folder ini?')">
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

    {{-- Files Section --}}
    @if($isi_folder->files->count())
    <div>
        <div class="flex items-center gap-2 mb-4">
            <svg class="w-4 h-4 text-[#2563eb]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            <h2 class="text-xs font-semibold text-[#64748b] uppercase tracking-wider">File</h2>
            <span class="text-xs text-[#94a3b8] bg-[#f1f5f9] px-2 py-0.5 rounded-full">{{ $isi_folder->files->count() }}</span>
        </div>

        {{-- Grid View --}}
        <div id="grid-view" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
            @foreach ($isi_folder->files as $file)
            @php $ext = strtolower(pathinfo($file->nama_tampilan, PATHINFO_EXTENSION)); @endphp
            <div class="file-card bg-white border border-[#e2e8f0] rounded-xl p-4 group">
                <div class="w-full h-28 bg-[#f8fafc] rounded-lg flex items-center justify-center mb-3 overflow-hidden">
                    @if(in_array($ext, ['jpg','jpeg','png','gif','svg','webp']))
                        <div class="w-full h-full bg-[#f3e8ff] flex items-center justify-center">
                            <svg class="w-10 h-10 text-[#8b5cf6]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @elseif(in_array($ext, ['mp4','webm','mov','avi']))
                        <div class="w-full h-full bg-[#fef3c7] flex items-center justify-center">
                            <svg class="w-10 h-10 text-[#f59e0b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </div>
                    @elseif($ext === 'pdf')
                        <div class="w-full h-full bg-[#fef2f2] flex items-center justify-center">
                            <svg class="w-10 h-10 text-[#ef4444]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                    @else
                        <div class="w-full h-full bg-[#eff6ff] flex items-center justify-center">
                            <svg class="w-10 h-10 text-[#2563eb]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                </div>
                <a href="/open_file/{{ $file->id }}" class="block">
                    <p class="text-sm font-medium text-[#0f172a] truncate group-hover:text-[#2563eb]">{{ $file->nama_tampilan }}</p>
                    <div class="flex items-center justify-between mt-1.5">
                        <span class="text-xs text-[#94a3b8]">{{ $file->ukuran_format }}</span>
                    </div>
                </a>
                <div class="flex items-center justify-end gap-1 pt-3 mt-3 border-t border-[#f1f5f9]">
                    <a href="/download/{{ $file->id }}" class="p-1.5 text-[#94a3b8] hover:text-green-600 hover:bg-green-50 rounded-lg" title="Download">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </a>
                    <a href="/rename_file/{{ $file->id }}" class="p-1.5 text-[#94a3b8] hover:text-[#2563eb] hover:bg-[#eff6ff] rounded-lg" title="Rename">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    <a href="/permissions_file/{{ $file->id }}" class="p-1.5 text-[#94a3b8] hover:text-[#2563eb] hover:bg-[#eff6ff] rounded-lg" title="Bagikan">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                    </a>
                    <a href="/hapus_file/{{ $file->id }}" class="p-1.5 text-[#94a3b8] hover:text-red-500 hover:bg-red-50 rounded-lg" title="Hapus" onclick="return confirm('Hapus file ini?')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        {{-- List View --}}
        <div id="list-view" class="hidden">
            <div class="bg-white border border-[#e2e8f0] rounded-xl overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-[#f8fafc] text-[#64748b] text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 font-medium">Nama File</th>
                            <th class="px-6 py-3 font-medium hidden sm:table-cell">Ukuran</th>
                            <th class="px-6 py-3 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f1f5f9]">
                        @foreach ($isi_folder->files as $file)
                        @php $ext = strtolower(pathinfo($file->nama_tampilan, PATHINFO_EXTENSION)); @endphp
                        <tr class="hover:bg-[#f8fafc]">
                            <td class="px-6 py-4">
                                <a href="/open_file/{{ $file->id }}" class="flex items-center gap-3 group">
                                    <div class="w-8 h-8 bg-[#f1f5f9] rounded-lg flex items-center justify-center
                                        @if(in_array($ext, ['jpg','jpeg','png','gif','svg','webp'])) text-[#8b5cf6]
                                        @elseif(in_array($ext, ['mp4','webm','mov','avi'])) text-[#f59e0b]
                                        @elseif($ext === 'pdf') text-[#ef4444]
                                        @else text-[#2563eb]
                                        @endif">
                                        @if(in_array($ext, ['jpg','jpeg','png','gif','svg','webp']))
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        @elseif(in_array($ext, ['mp4','webm','mov','avi']))
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        @elseif($ext === 'pdf')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        @endif
                                    </div>
                                    <span class="text-[#0f172a] group-hover:text-[#2563eb] transition-colors truncate max-w-[150px] sm:max-w-xs">{{ $file->nama_tampilan }}</span>
                                </a>
                            </td>
                            <td class="px-6 py-4 text-[#64748b] hidden sm:table-cell">{{ $file->ukuran_format }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="/download/{{ $file->id }}" class="p-1.5 text-[#94a3b8] hover:text-green-600 hover:bg-green-50 rounded-lg" title="Download">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    </a>
                                    <a href="/rename_file/{{ $file->id }}" class="p-1.5 text-[#94a3b8] hover:text-[#2563eb] hover:bg-[#eff6ff] rounded-lg" title="Rename">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <a href="/permissions_file/{{ $file->id }}" class="p-1.5 text-[#94a3b8] hover:text-[#2563eb] hover:bg-[#eff6ff] rounded-lg" title="Bagikan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                                    </a>
                                    <a href="/hapus_file/{{ $file->id }}" class="p-1.5 text-[#94a3b8] hover:text-red-500 hover:bg-red-50 rounded-lg" title="Hapus" onclick="return confirm('Hapus file ini?')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Empty State --}}
    @if(!$isi_folder->children->count() && !$isi_folder->files->count())
    <div class="flex flex-col items-center justify-center py-24 bg-white border border-[#e2e8f0] rounded-xl text-center">
        <div class="w-16 h-16 bg-[#f1f5f9] rounded-2xl flex items-center justify-center mb-5">
            <svg class="w-8 h-8 text-[#94a3b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
        </div>
        <h3 class="text-[#0f172a] font-semibold text-lg mb-2">Folder Kosong</h3>
        <p class="text-sm text-[#64748b] mb-6 max-w-xs">Upload file atau buat subfolder di sini.</p>
        <div class="flex items-center gap-3">
            <form action="/upload" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="folder_id" value="{{ $isi_folder->id }}">
                <label class="cursor-pointer inline-flex items-center gap-2 px-5 py-2.5 bg-[#2563eb] hover:bg-[#1d4ed8] text-white text-sm font-medium rounded-xl transition-all hover:shadow-md hover:shadow-[#2563eb]/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"/>
                    </svg>
                    Upload File
                    <input type="file" name="upload" class="hidden" onchange="this.form.submit()">
                </label>
            </form>
            <button onclick="document.getElementById('new-folder-modal').classList.remove('hidden')" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#f1f5f9] hover:bg-[#e2e8f0] text-sm font-medium text-[#0f172a] rounded-xl transition-colors border border-[#e2e8f0]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Subfolder
            </button>
        </div>
    </div>
    @endif

    {{-- Grid/List Toggle Script --}}
    <script>
        function switchView(view) {
            const gridView = document.getElementById('grid-view');
            const listView = document.getElementById('list-view');
            const gridBtn = document.getElementById('grid-btn');
            const listBtn = document.getElementById('list-btn');
            if (!gridView || !listView) return;
            if (view === 'grid') {
                gridView.classList.remove('hidden');
                listView.classList.add('hidden');
                gridBtn.classList.add('active');
                listBtn.classList.remove('active');
            } else {
                gridView.classList.add('hidden');
                listView.classList.remove('hidden');
                listBtn.classList.add('active');
                gridBtn.classList.remove('active');
            }
            localStorage.setItem('fileView', view);
        }
        document.addEventListener('DOMContentLoaded', () => {
            const savedView = localStorage.getItem('fileView');
            if (savedView) switchView(savedView);
        });
    </script>

@endsection
