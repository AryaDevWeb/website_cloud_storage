@extends('layouts.app')
@section('title', 'Dashboard — Cloud Storage')

@section('content')

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- PAGE HEADER                                               --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        @php $hour = \Carbon\Carbon::now()->hour; @endphp
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
            Good {{ $hour < 12 ? 'morning' : ($hour < 17 ? 'afternoon' : 'evening') }}, {{ explode(' ', auth()->user()->name)[0] }} 👋
        </h1>
        <p class="text-sm text-gray-500 mt-1">Here's what's happening with your storage today.</p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="document.getElementById('new-folder-modal').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            </svg>
            New Folder
        </button>
        <button data-upload-trigger
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md shadow-blue-500/25 hover:shadow-blue-500/40 hover:scale-[1.02] active:scale-[0.98]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"/>
            </svg>
            Upload Files
        </button>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- SUMMARY CARDS (4-up)                                      --}}
{{-- ══════════════════════════════════════════════════════════ --}}
@php
    $cards = [
        [
            'label'   => 'Total Files',
            'value'   => number_format($totalFiles ?? 0),
            'sub'     => ($totalFolders ?? 0) . ' folder' . (($totalFolders ?? 0) != 1 ? 's' : ''),
            'bg'      => 'bg-blue-50',
            'ring'    => 'ring-blue-100',
            'icon_bg' => 'bg-blue-600',
            'shadow'  => 'shadow-blue-100',
            'icon'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>',
        ],
        [
            'label'   => 'Storage Used',
            'value'   => ($usedMB ?? '0') . ' MB',
            'sub'     => number_format($percentage ?? 0, 1) . '% of ' . ($totalMB ?? 0) . ' MB',
            'bg'      => 'bg-violet-50',
            'ring'    => 'ring-violet-100',
            'icon_bg' => 'bg-violet-600',
            'shadow'  => 'shadow-violet-100',
            'icon'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>',
        ],
        [
            'label'   => 'Remaining',
            'value'   => ($remainingMB ?? '0') . ' MB',
            'sub'     => 'Free space available',
            'bg'      => 'bg-emerald-50',
            'ring'    => 'ring-emerald-100',
            'icon_bg' => 'bg-emerald-500',
            'shadow'  => 'shadow-emerald-100',
            'icon'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
        ],
        [
            'label'   => 'Recent Uploads',
            'value'   => number_format(isset($recentFiles) ? $recentFiles->count() : 0),
            'sub'     => 'In the last activity',
            'bg'      => 'bg-amber-50',
            'ring'    => 'ring-amber-100',
            'icon_bg' => 'bg-amber-500',
            'shadow'  => 'shadow-amber-100',
            'icon'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"/>',
        ],
    ];
@endphp

<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
    @foreach($cards as $i => $card)
    <div class="stat-card rounded-2xl p-5 shadow-sm {{ $card['shadow'] }} ring-1 {{ $card['ring'] }} {{ $card['bg'] }} hover:shadow-md transition-all duration-200 group">
        <div class="flex items-start justify-between mb-4">
            <div class="w-10 h-10 {{ $card['icon_bg'] }} rounded-xl flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-200">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    {!! $card['icon'] !!}
                </svg>
            </div>
            <span class="text-xs font-medium text-gray-400 bg-white/60 px-2 py-0.5 rounded-lg">{{ sprintf('%02d', $i+1) }}</span>
        </div>
        <p class="text-2xl font-bold text-gray-900 mb-1 tracking-tight">{{ $card['value'] }}</p>
        <p class="text-xs font-semibold text-gray-600 mb-0.5">{{ $card['label'] }}</p>
        <p class="text-xs text-gray-400">{{ $card['sub'] }}</p>
    </div>
    @endforeach
</div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- MIDDLE ROW: Storage Chart + Recent Activity              --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-8">

    {{-- Storage Overview chart (3/5) --}}
    <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-base font-bold text-gray-900">Storage Overview</h2>
                <p class="text-xs text-gray-400 mt-0.5">Breakdown by file type</p>
            </div>
            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 bg-gray-50 border border-gray-100 px-3 py-1.5 rounded-lg">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                {{ number_format($percentage ?? 0, 1) }}% used
            </span>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-6">
            {{-- Donut chart --}}
            <div class="relative shrink-0 flex items-center justify-center" style="width:180px;height:180px">
                <canvas id="storageChart" width="180" height="180"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-2xl font-bold text-gray-900">{{ number_format($percentage ?? 0, 0) }}%</span>
                    <span class="text-xs text-gray-400 font-medium">Used</span>
                </div>
            </div>
            {{-- Legend --}}
            <div class="flex-1 grid grid-cols-1 gap-3 w-full">
                @php
                    $usedBytes = auth()->user()->storage_used ?? 0;
                    $quotaBytes = auth()->user()->storage_quota ?? 1;
                    $freeBytes = max(0, $quotaBytes - $usedBytes);
                    
                    // Breakdown provided from controller: ['Images'=>val, 'Videos'=>val, 'PDFs'=>val, 'Docs'=>val, 'Others'=>val]
                    $colors = [
                        'Images' => '#8b5cf6',
                        'Videos' => '#f59e0b',
                        'PDFs'   => '#ef4444',
                        'Docs'   => '#3b82f6',
                        'Others' => '#10b981'
                    ];

                    $stats = [];
                    foreach($breakdown as $label => $bytes) {
                        $pct = $usedBytes > 0 ? ($bytes / $usedBytes) * 100 : 0;
                        if($pct > 0 || $bytes > 0) {
                            $stats[] = ['label' => $label, 'color' => $colors[$label], 'pct' => round($pct, 1), 'bytes' => $bytes];
                        }
                    }
                @endphp
                @foreach($stats as $leg)
                <div class="flex items-center justify-between p-2.5 rounded-xl hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full shrink-0" style="background:{{ $leg['color'] }}"></span>
                        <span class="text-sm font-medium text-gray-700">{{ $leg['label'] }}</span>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="text-xs font-semibold text-gray-500">{{ $leg['pct'] }}%</span>
                        <span class="text-[10px] text-gray-400">{{ number_format($leg['bytes'] / 1024 / 1024, 1) }} MB</span>
                    </div>
                </div>
                @endforeach
                <div class="flex items-center justify-between p-2.5 rounded-xl hover:bg-gray-50 transition-colors border-t border-dashed border-gray-100 pt-3">
                    <div class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full shrink-0 bg-gray-200"></span>
                        <span class="text-sm font-medium text-gray-400">Free</span>
                    </div>
                    <span class="text-xs font-semibold text-gray-400">{{ $quotaBytes > 0 ? number_format((($quotaBytes - $usedBytes) / $quotaBytes) * 100, 0) : 100 }}%</span>
                </div>
            </div>
        </div>

        {{-- Usage bar --}}
        <div class="mt-6 pt-5 border-t border-gray-100">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                <span class="font-medium text-gray-700">{{ $usedMB ?? 0 }} MB used</span>
                <span>{{ $totalMB ?? 0 }} MB total</span>
            </div>
            <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden" role="progressbar"
                 aria-valuenow="{{ round($percentage ?? 0) }}" aria-valuemin="0" aria-valuemax="100">
                <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 transition-all duration-700"
                     style="width:{{ min($percentage ?? 0, 100) }}%"></div>
            </div>
        </div>
    </div>

    {{-- Recent Activity (2/5) --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-base font-bold text-gray-900">Recent Activity</h2>
                <p class="text-xs text-gray-400 mt-0.5">Your latest file actions</p>
            </div>
            <a href="/beranda/{{ auth()->id() }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors">View all →</a>
        </div>

        @if(isset($recentFiles) && $recentFiles->count())
        <div class="flex-1 space-y-1">
            @foreach($recentFiles->take(5) as $file)
            @php
                $ext = strtolower(pathinfo($file->nama_tampilan, PATHINFO_EXTENSION));
                $iconConfig = match(true) {
                    in_array($ext, ['jpg','jpeg','png','gif','svg','webp']) => ['bg' => 'bg-violet-100', 'color' => 'text-violet-500'],
                    in_array($ext, ['mp4','webm','mov','avi'])             => ['bg' => 'bg-amber-100',  'color' => 'text-amber-500'],
                    $ext === 'pdf'                                          => ['bg' => 'bg-red-100',    'color' => 'text-red-500'],
                    default                                                 => ['bg' => 'bg-blue-100',   'color' => 'text-blue-500'],
                };
            @endphp
            <a href="/open_file/{{ $file->id }}"
               class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors group">
                <div class="w-9 h-9 {{ $iconConfig['bg'] }} rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 {{ $iconConfig['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate group-hover:text-blue-600 transition-colors">
                        {{ $file->nama_tampilan }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">Uploaded · {{ $file->created_at->diffForHumans() }}</p>
                </div>
                <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-500 shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            @endforeach
        </div>
        @else
        <div class="flex-1 flex flex-col items-center justify-center text-center py-8">
            <div class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center mb-3">
                <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500">No recent files</p>
            <p class="text-xs text-gray-400 mt-1">Upload your first file to get started</p>
        </div>
        @endif

        {{-- Quick actions --}}
        <div class="mt-5 pt-4 border-t border-gray-100 grid grid-cols-2 gap-2">
            <button onclick="document.getElementById('file-input')?.click()"
                    class="flex items-center justify-center gap-2 py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-xl transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"/>
                </svg>
                Upload
            </button>
            <button onclick="document.getElementById('new-folder-modal').classList.remove('hidden')"
                    class="flex items-center justify-center gap-2 py-2.5 bg-gray-50 hover:bg-gray-100 text-gray-700 text-xs font-semibold rounded-xl transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                </svg>
                Folder
            </button>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- RECENT FILES GRID                                         --}}
{{-- ══════════════════════════════════════════════════════════ --}}
@if(isset($recentFiles) && $recentFiles->count())
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
        <div>
            <h2 class="text-base font-bold text-gray-900">Recent Files</h2>
            <p class="text-xs text-gray-400 mt-0.5">Your most recently added files</p>
        </div>
        <a href="/beranda/{{ auth()->id() }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-all">
            View All Files
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="bg-gray-50/70 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-100">
                    <th class="px-6 py-3 font-semibold">File Name</th>
                    <th class="px-6 py-3 font-semibold hidden sm:table-cell">Type</th>
                    <th class="px-6 py-3 font-semibold hidden md:table-cell">Size</th>
                    <th class="px-6 py-3 font-semibold hidden lg:table-cell">Uploaded</th>
                    <th class="px-6 py-3 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($recentFiles as $file)
                @php
                    $ext = strtolower(pathinfo($file->nama_tampilan, PATHINFO_EXTENSION));
                    $fileConfig = match(true) {
                        in_array($ext, ['jpg','jpeg','png','gif','svg','webp']) => ['bg' => 'bg-violet-50', 'color' => 'text-violet-500', 'badge' => 'bg-violet-50 text-violet-600', 'label' => 'Image'],
                        in_array($ext, ['mp4','webm','mov','avi'])             => ['bg' => 'bg-amber-50',  'color' => 'text-amber-500',  'badge' => 'bg-amber-50 text-amber-600',  'label' => 'Video'],
                        $ext === 'pdf'                                          => ['bg' => 'bg-red-50',    'color' => 'text-red-500',    'badge' => 'bg-red-50 text-red-600',    'label' => 'PDF'],
                        default                                                 => ['bg' => 'bg-blue-50',   'color' => 'text-blue-500',   'badge' => 'bg-blue-50 text-blue-600',  'label' => strtoupper($ext ?: 'File')],
                    };
                @endphp
                <tr class="hover:bg-blue-50/30 transition-colors group">
                    <td class="px-6 py-4">
                        <a href="/open_file/{{ $file->id }}" class="flex items-center gap-3">
                            <div class="w-9 h-9 {{ $fileConfig['bg'] }} rounded-xl flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4 {{ $fileConfig['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <span class="font-medium text-gray-800 group-hover:text-blue-600 transition-colors truncate max-w-[180px]">
                                {{ $file->nama_tampilan }}
                            </span>
                        </a>
                    </td>
                    <td class="px-6 py-4 hidden sm:table-cell">
                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-lg {{ $fileConfig['badge'] }}">
                            {{ $fileConfig['label'] }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-500 hidden md:table-cell text-xs">
                        {{ $file->ukuran_format ?? number_format($file->ukuran / 1024, 1) . ' KB' }}
                    </td>
                    <td class="px-6 py-4 text-gray-400 hidden lg:table-cell text-xs">
                        {{ $file->created_at->format('d M Y') }}
                        <span class="block text-gray-300">{{ $file->created_at->format('H:i') }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-1">
                            <a href="/open_file/{{ $file->id }}"
                               class="p-2 text-gray-300 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" aria-label="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="/download/{{ $file->id }}"
                               class="p-2 text-gray-300 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" aria-label="Download">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                            </a>
                            <a href="/hapus_file/{{ $file->id }}"
                               onclick="return confirm('Delete {{ addslashes($file->nama_tampilan) }}?')"
                               class="p-2 text-gray-300 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" aria-label="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Footer --}}
    <div class="px-6 py-3 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between">
        <p class="text-xs text-gray-400">Showing {{ $recentFiles->count() }} most recent files</p>
        <a href="/beranda/{{ auth()->id() }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors">
            Browse all files →
        </a>
    </div>
</div>
@else
{{-- Empty state --}}
<div class="bg-white rounded-2xl border border-dashed border-gray-200 p-12 flex flex-col items-center justify-center text-center">
    <div class="w-20 h-20 bg-blue-50 rounded-3xl flex items-center justify-center mb-5">
        <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
        </svg>
    </div>
    <h3 class="text-lg font-bold text-gray-900 mb-2">No files yet</h3>
    <p class="text-sm text-gray-500 max-w-sm mb-6">Upload your first file to get started. Supports images, PDFs, videos and more.</p>
    <button onclick="document.getElementById('file-input')?.click()"
            class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md shadow-blue-500/25 hover:scale-[1.02]">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"/>
        </svg>
        Upload Your First File
    </button>
</div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Storage Donut Chart ───────────────────────────────────
    const ctx = document.getElementById('storageChart');
    if (ctx) {
        // Real data from PHP
        const chartData = @json($breakdown);
        const quota = {{ auth()->user()->storage_quota }};
        const used  = {{ auth()->user()->storage_used }};
        const free  = Math.max(0, quota - used);

        // Convert bytes to percentages relative to TOTAL QUOTA for the chart segments
        const getPct = bytes => quota > 0 ? (bytes / quota) * 100 : 0;

        const labels = Object.keys(chartData);
        const values = Object.values(chartData).map(getPct);
        
        // Add Free space
        labels.push('Free');
        values.push(getPct(free));

        // Sync colors with legend
        const colors = ['#8b5cf6', '#f59e0b', '#ef4444', '#3b82f6', '#10b981', '#f1f5f9'];

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values.map(v => v.toFixed(1)),
                    backgroundColor: colors,
                    borderColor: colors.map(c => c === '#f1f5f9' ? '#e2e8f0' : '#fff'),
                    borderWidth: 3,
                    hoverOffset: 6,
                }]
            },
            options: {
                cutout: '72%',
                responsive: false,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.parsed}%`
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    duration: 900,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }
});
</script>
@endpush
