@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    <div class="mb-8">
        <h1 class="text-xl font-semibold text-gray-800">Welcome, {{ auth()->user()->name }} 👋</h1>
        <p class="text-sm text-gray-500 mt-1">Here's your storage overview</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @php $stats = [
            ['val' => $totalFiles ?? 0, 'label' => 'Total Files', 'bg' => 'bg-blue-50', 'color' => 'text-blue-600', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>'],
            ['val' => $totalFolders ?? 0, 'label' => 'Total Folders', 'bg' => 'bg-amber-50', 'color' => 'text-amber-500', 'icon' => '<path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>', 'fill' => true],
            ['val' => ($usedMB ?? '0') . ' MB', 'label' => 'Used', 'bg' => 'bg-green-50', 'color' => 'text-green-500', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>'],
            ['val' => ($remainingMB ?? '0') . ' MB', 'label' => 'Remaining', 'bg' => 'bg-violet-50', 'color' => 'text-violet-500', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'],
        ]; @endphp
        @foreach($stats as $s)
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="w-10 h-10 {{ $s['bg'] }} rounded-xl flex items-center justify-center mb-3">
                <svg class="w-5 h-5 {{ $s['color'] }}" {{ isset($s['fill']) ? 'fill=currentColor' : 'fill=none stroke=currentColor' }} viewBox="0 0 {{ isset($s['fill']) ? '20 20' : '24 24' }}">{!! $s['icon'] !!}</svg>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $s['val'] }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $s['label'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Storage bar --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5 mb-8">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-semibold text-gray-800">Storage Usage</h2>
            <span class="text-xs font-medium text-gray-500">{{ number_format($percentage ?? 0, 1) }}%</span>
        </div>
        <div class="h-2.5 w-full bg-gray-100 rounded-full overflow-hidden" role="progressbar" aria-valuenow="{{ round($percentage ?? 0) }}" aria-valuemin="0" aria-valuemax="100">
            <div class="h-full rounded-full transition-all {{ ($percentage ?? 0) > 90 ? 'bg-red-500' : (($percentage ?? 0) > 70 ? 'bg-amber-500' : 'bg-blue-600') }}" style="width:{{ min($percentage ?? 0, 100) }}%"></div>
        </div>
        <div class="flex justify-between mt-2">
            <span class="text-xs text-gray-500">{{ $usedMB ?? 0 }} MB used</span>
            <span class="text-xs text-gray-500">{{ $totalMB ?? 0 }} MB total</span>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
        <button onclick="document.getElementById('file-input')?.click()" class="flex items-center gap-4 p-5 bg-white border border-gray-200 rounded-xl hover:border-blue-300 hover:bg-blue-50/50 text-left group transition-colors">
            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center group-hover:bg-blue-600 transition-colors"><svg class="w-6 h-6 text-blue-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"/></svg></div>
            <div><p class="text-sm font-semibold text-gray-800">Upload File</p><p class="text-xs text-gray-500">Add files to your storage</p></div>
        </button>
        <button onclick="document.getElementById('new-folder-modal').classList.remove('hidden')" class="flex items-center gap-4 p-5 bg-white border border-gray-200 rounded-xl hover:border-amber-300 hover:bg-amber-50/50 text-left group transition-colors">
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center group-hover:bg-amber-500 transition-colors"><svg class="w-6 h-6 text-amber-500 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg></div>
            <div><p class="text-sm font-semibold text-gray-800">Create Folder</p><p class="text-xs text-gray-500">Organize your files</p></div>
        </button>
    </div>

    {{-- Recent files --}}
    @if(isset($recentFiles) && $recentFiles->count())
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-800">Recent Files</h2>
            <a href="/beranda/{{ auth()->id() }}" class="text-xs text-blue-600 hover:text-blue-700 font-medium">View All →</a>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <tr><th class="px-6 py-3 font-medium">Name</th><th class="px-6 py-3 font-medium hidden sm:table-cell">Size</th><th class="px-6 py-3 font-medium hidden md:table-cell">Date</th><th class="px-6 py-3 font-medium text-right">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($recentFiles as $file)
                    @php $ext = strtolower(pathinfo($file->nama_tampilan, PATHINFO_EXTENSION)); @endphp
                    <tr class="hover:bg-gray-50 transition-colors" data-file-id="{{ $file->id }}">
                        <td class="px-6 py-4"><a href="/open_file/{{ $file->id }}" class="flex items-center gap-3 group">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 @if(in_array($ext,['jpg','jpeg','png','gif','svg','webp'])) bg-violet-50 text-violet-400 @elseif(in_array($ext,['mp4','webm','mov','avi'])) bg-amber-50 text-amber-400 @elseif($ext==='pdf') bg-red-50 text-red-400 @else bg-blue-50 text-blue-400 @endif">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <span class="text-gray-800 group-hover:text-blue-600 transition-colors truncate max-w-[150px] sm:max-w-xs font-medium">{{ $file->nama_tampilan }}</span>
                        </a></td>
                        <td class="px-6 py-4 text-gray-500 hidden sm:table-cell">{{ $file->ukuran_format }}</td>
                        <td class="px-6 py-4 text-gray-500 hidden md:table-cell">{{ $file->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-4"><div class="flex items-center justify-end gap-1">
                            <a href="/download/{{ $file->id }}" class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors" aria-label="Download"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg></a>
                            <a href="/open_file/{{ $file->id }}" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" aria-label="Open"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                        </div></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
@endsection
