@extends('layouts.app')
@section('title', 'My Files')

@section('content')
<div data-spa-explorer>
    {{-- Header + Toolbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-semibold text-gray-800">My Files</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage your files and folders</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">

            <select id="sort-select" class="appearance-none px-3 py-2 pr-8 bg-white border border-gray-200 rounded-xl text-sm text-gray-600 cursor-pointer focus:outline-none focus:border-blue-600" aria-label="Sort by">
                <option value="name">Name</option>
                <option value="date">Date</option>
                <option value="size">Size</option>
            </select>
            <div class="flex items-center bg-gray-100 border border-gray-200 rounded-xl p-0.5" role="radiogroup" aria-label="View mode">
                <button id="grid-btn" class="p-1.5 rounded-lg transition-colors bg-white shadow-sm text-blue-600" role="radio" aria-checked="true" aria-label="Grid">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                </button>
                <button id="list-btn" class="p-1.5 rounded-lg transition-colors text-gray-400" role="radio" aria-checked="false" aria-label="List">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                </button>
            </div>
        </div>
    </div>

    @if (session('status_file'))
        <p class="mb-4 text-sm text-green-700 bg-green-50 border border-green-100 rounded-lg px-3 py-2">{{ session('status_file') }}</p>
    @endif

    <div id="loading-spinner" class="hidden flex items-center justify-center py-16">
        <svg class="animate-spin w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
    </div>

    <div id="grid-view" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3" role="listbox" aria-label="Files and folders"></div>

    <div id="list-view" class="hidden">
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <table class="w-full text-left text-sm" role="table">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 w-10"><input type="checkbox" id="select-all" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" aria-label="Select all"></th>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium hidden sm:table-cell">Owner</th>
                        <th class="px-4 py-3 font-medium hidden md:table-cell">Size</th>
                        <th class="px-4 py-3 font-medium hidden lg:table-cell">Modified</th>
                        <th class="px-4 py-3 w-10"></th>
                    </tr>
                </thead>
                <tbody id="list-body" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>
    </div>

    <div id="empty-state" class="hidden flex flex-col items-center justify-center py-24 bg-white border border-gray-200 rounded-xl text-center">
        <div class="w-14 h-14 bg-gray-100 rounded-xl flex items-center justify-center mb-5">
            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-800 mb-1">No files yet</h3>
        <p class="text-sm text-gray-500 mb-6 max-w-xs">Upload your first file or create a folder to get started.</p>

    </div>

    <div id="pagination" class="flex justify-center mt-6"></div>
</div>
@endsection

@push('scripts')
<script>
    window.__FILE_SECTION__ = 'files';
    window.__TRASH_MODE__ = false;
</script>
@endpush
