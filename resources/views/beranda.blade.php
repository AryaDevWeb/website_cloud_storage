@extends('layouts.app')
@section('title', 'My Files')

@section('content')
    {{-- Header + Toolbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-semibold text-gray-800">My Files</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage your files and folders</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <button onclick="document.getElementById('new-folder-modal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 border border-gray-200 rounded-xl transition-colors" aria-label="New folder">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                <span class="hidden sm:inline">New Folder</span>
            </button>
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

    {{-- Loading spinner --}}
    <div id="loading-spinner" class="hidden flex items-center justify-center py-16">
        <svg class="animate-spin w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
    </div>

    {{-- Grid view (JS-rendered) --}}


    {{-- List view (JS-rendered) --}}
    <div class="hidden">
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

    {{-- melihat isi file dan folder user  --}}

    @if (session("status_file"))
        <p>{{session("status_file") }}</p>
    
    @endif

    @foreach ($file as $files )
         <button>{{ $files->nama_tampilan }}</button>
         <p>{{ $files->path }}</p>

         <form action="/hapus_file/{{ $files->id }}">
            <button>Delete</button>

         </form>
         
         <button>Rename</button>
         
          <div  class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3" role="listbox" aria-label="Files and folders"></div>
         
    
    @endforeach

    @foreach ($folders as $folder_user)
        <button>{{ $folder_user->nama_folder }}</button>
         <div  class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3" role="listbox" aria-label="Files and folders"></div>
    
    @endforeach

    {{-- Empty state --}}
    @if (empty($file) && empty($folder))
         <div id="empty-state" class="hidden flex flex-col items-center justify-center py-24 bg-white border border-gray-200 rounded-xl text-center">
        <div class="w-14 h-14 bg-gray-100 rounded-xl flex items-center justify-center mb-5">
            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-800 mb-1">No files yet</h3>
        <p class="text-sm text-gray-500 mb-6 max-w-xs">Upload your first file or create a folder to get started.</p>
        <div class="flex items-center gap-3">
            <button onclick="document.getElementById('file-input')?.click()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-colors">
                
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"/></svg>
                Upload
            </button>
            <button onclick="document.getElementById('new-folder-modal').classList.remove('hidden')" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white hover:bg-gray-50 text-sm font-medium text-gray-700 rounded-xl border border-gray-200 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                New Folder
            </button>
        </div>
    </div>
    
    @endif
   

    {{-- Pagination --}}
    <div id="pagination" class="flex justify-center mt-6"></div>
@endsection