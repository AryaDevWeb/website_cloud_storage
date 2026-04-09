@extends('layouts.app')
@section('title', $pageTitle ?? 'Files')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900">{{ $pageTitle ?? 'Files' }}</h1>
        <p class="text-sm text-gray-400 mt-0.5">{{ $pageDesc ?? '' }}</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        @if(!($trashMode ?? false))
        <button onclick="document.getElementById('new-folder-modal').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 border border-gray-200 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
            <span class="hidden sm:inline">New Folder</span>
        </button>
        @endif
        @if($trashMode ?? false)
        <button id="empty-trash-btn"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Empty Trash
        </button>
        @endif
        <select id="sort-select" class="appearance-none px-3 py-2 pr-8 bg-white border border-gray-200 rounded-xl text-sm text-gray-600 cursor-pointer focus:outline-none focus:border-blue-600">
            <option value="name">Name</option>
            <option value="date">Date</option>
            <option value="size">Size</option>
        </select>
        <div class="flex items-center bg-gray-100 border border-gray-200 rounded-xl p-0.5">
            <button id="grid-btn" class="p-1.5 rounded-lg transition-colors bg-white shadow-sm text-blue-600">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            </button>
            <button id="list-btn" class="p-1.5 rounded-lg transition-colors text-gray-400">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            </button>
        </div>
    </div>
</div>

<div id="loading-spinner" class="hidden flex items-center justify-center py-16">
    <svg class="animate-spin w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
</div>

<div id="grid-view" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3" role="listbox"></div>

<div id="list-view" class="hidden">
    <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm">
        <table class="w-full text-left text-sm" role="table">
            <thead class="bg-gray-50/80 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-100">
                <tr>
                    <th class="px-4 py-3 w-10"><input type="checkbox" id="select-all" class="w-4 h-4 rounded border-gray-300 text-blue-600"></th>
                    <th class="px-4 py-3 font-semibold">Name</th>
                    <th class="px-4 py-3 font-semibold hidden sm:table-cell">Owner</th>
                    <th class="px-4 py-3 font-semibold hidden md:table-cell">Size</th>
                    <th class="px-4 py-3 font-semibold hidden lg:table-cell">Modified</th>
                    <th class="px-4 py-3 w-10"></th>
                </tr>
            </thead>
            <tbody id="list-body" class="divide-y divide-gray-50"></tbody>
        </table>
    </div>
</div>

<div id="empty-state" class="hidden flex flex-col items-center justify-center py-24 bg-white border border-dashed border-gray-200 rounded-2xl text-center">
    <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mb-4 float-icon">
        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $emptyIcon ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>' !!}
        </svg>
    </div>
    <h3 class="text-base font-bold text-gray-700 mb-1">{{ $emptyTitle ?? 'Nothing here' }}</h3>
    <p class="text-sm text-gray-400 max-w-xs">{{ $emptyDesc ?? '' }}</p>
    @if(!($trashMode ?? false))
    <button onclick="document.getElementById('file-input')?.click()" class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"/></svg>
        Upload File
    </button>
    @endif
</div>

<div id="pagination" class="flex justify-center mt-6"></div>
@endsection

@push('scripts')
<script>
    window.__FILE_SECTION__ = @json($section ?? 'files');
    window.__TRASH_MODE__   = @json($trashMode ?? false);
</script>
@endpush
