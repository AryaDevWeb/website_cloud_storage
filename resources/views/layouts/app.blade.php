<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Cloud Storage')</title>
    <meta name="description" content="Secure cloud storage — manage, upload, and share your files.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen font-[Inter] text-gray-800">

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- DRAG & DROP OVERLAY                                    --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div id="drop-overlay" class="fixed inset-0 z-[60] bg-blue-600/5 border-[3px] border-dashed border-blue-600 flex items-center justify-center pointer-events-none opacity-0 transition-opacity duration-200" aria-hidden="true">
    <div class="bg-white px-10 py-8 rounded-2xl text-center border border-gray-200 shadow-lg">
        <div class="w-14 h-14 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
        </div>
        <h2 class="text-lg font-semibold text-gray-800 mb-1">Drop files to upload</h2>
        <p class="text-sm text-gray-500">Your files will be saved immediately</p>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- UPLOAD PROGRESS PANEL                                  --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div id="upload-progress-panel" class="hidden fixed bottom-20 right-6 z-[70] w-80 bg-white rounded-xl border border-gray-200 shadow-lg overflow-hidden" role="log" aria-label="Upload progress">
    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
        <span class="text-sm font-semibold text-gray-800" id="upload-progress-title">Uploading…</span>
        <button onclick="document.getElementById('upload-progress-panel').classList.add('hidden')" class="p-1 text-gray-400 hover:text-gray-600 rounded" aria-label="Close">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <div id="upload-progress-list" class="max-h-48 overflow-y-auto"></div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- TOAST CONTAINER                                        --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div id="toast-container" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[100] flex flex-col items-center gap-2 pointer-events-none" role="region" aria-label="Notifications"></div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- SELECTION BAR (bottom)                                  --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div id="selection-bar" class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-50 flex items-center gap-2 px-4 py-3 bg-gray-800 text-white rounded-2xl shadow-lg">
    <span id="sel-count" class="text-sm font-medium mr-2">0 selected</span>
    <div class="w-px h-5 bg-white/20"></div>
    <button id="sel-download" class="p-2 rounded-lg hover:bg-white/10 transition-colors" aria-label="Download selected">
        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
    </button>
    <button id="sel-move" class="p-2 rounded-lg hover:bg-white/10 transition-colors" aria-label="Move selected">
        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
    </button>
    <button id="sel-delete" class="p-2 rounded-lg hover:bg-white/10 transition-colors" aria-label="Delete selected">
        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
    </button>
    <div class="w-px h-5 bg-white/20"></div>
    <button id="sel-clear" class="p-2 rounded-lg hover:bg-white/10 transition-colors" aria-label="Clear selection">
        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- TOP NAVBAR                                             --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<header class="fixed top-0 left-0 right-0 z-40 h-16 bg-white border-b border-gray-200" role="banner">
    <div class="h-full flex items-center justify-between px-4 lg:px-6">
        {{-- Left: burger (mobile) + logo --}}
        <div class="flex items-center gap-3">
            <button id="sidebar-toggle" class="lg:hidden p-2 text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors" aria-label="Toggle menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            {{-- Logo — desktop top-left --}}
            <a href="/" aria-label="Home" class="hidden lg:block">
                <img src="{{ asset('images/CLD.png') }}" alt="Logo" class="w-10 h-10 object-contain">
            </a>
        </div>

        {{-- Logo — mobile centered --}}
        <a href="/" class="lg:hidden absolute left-1/2 -translate-x-1/2" aria-label="Home">
            <img src="{{ asset('images/CLD.png') }}" alt="Logo" class="w-9 h-9 object-contain">
        </a>

        {{-- Center: Search (desktop) --}}
        <div class="hidden md:block flex-1 max-w-xl mx-8">
            <form class="relative" role="search">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input id="desktop-search" name="cari" type="text" placeholder="Search files or folders..." autocomplete="off"
                       class="w-full pl-10 pr-4 py-2.5 bg-gray-100 border border-transparent rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-600/10 transition-all" aria-label="Search">
            </form>
        </div>

        {{-- Right --}}
        <div class="flex items-center gap-1 sm:gap-2">
            <button id="mobile-search-toggle" class="md:hidden p-2 text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors" aria-label="Search">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </button>
            @auth
            <button id="upload-btn" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-colors" aria-label="Upload">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"/></svg>
                Upload
            </button>
            <input id="file-input" type="file" multiple class="hidden" aria-label="Choose files">
            <button class="relative p-2 text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors" aria-label="Notifications">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>
            <div class="relative" id="user-dropdown-wrapper">
                <button id="user-dropdown-btn" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-gray-100 transition-colors" aria-expanded="false" aria-label="User menu">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white text-xs font-semibold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <span class="hidden lg:inline text-sm font-medium text-gray-800 max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                    <svg class="hidden lg:block w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div id="user-dropdown" class="hidden absolute right-0 top-full mt-2 w-56 bg-white rounded-xl border border-gray-200 shadow-lg py-1.5 z-50" role="menu">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <p class="text-sm font-medium text-gray-800">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ auth()->user()->email ?? '' }}</p>
                    </div>
                    <a href="/lihat_akun/{{ auth()->id() }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-500 hover:text-gray-800 hover:bg-gray-50" role="menuitem">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profile
                    </a>
                    <form action="/logout" method="POST">@csrf
                        <button type="submit" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-gray-500 hover:text-red-600 hover:bg-red-50" role="menuitem">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
            @endauth
        </div>
    </div>
    {{-- Mobile search --}}
    <div id="mobile-search-bar" class="hidden md:hidden absolute top-full left-0 right-0 bg-white border-b border-gray-200 px-4 py-3">
        <form class="relative flex items-center gap-2" role="search">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input name="cari" type="text" placeholder="Search files or folders..." autofocus
                   class="flex-1 pl-10 pr-4 py-2.5 bg-gray-100 border border-blue-600 rounded-xl text-sm placeholder-gray-400 focus:outline-none transition-all" aria-label="Search">
            <button type="button" id="mobile-search-cancel" class="p-2 text-gray-500 hover:text-gray-800 rounded-lg shrink-0" aria-label="Cancel">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </form>
    </div>
</header>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- SIDEBAR                                                --}}
{{-- ═══════════════════════════════════════════════════════ --}}
@auth
<aside id="sidebar" class="fixed top-16 bottom-0 left-0 z-30 bg-white border-r border-gray-200 flex flex-col w-60 -translate-x-full lg:translate-x-0 transition-all duration-300" role="navigation" aria-label="Sidebar">
    <div class="hidden lg:flex items-center justify-end px-3 py-2">
        <button id="sidebar-collapse-btn" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" aria-label="Collapse sidebar">
            <svg class="w-4 h-4" id="collapse-icon-left" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            <svg class="w-4 h-4 hidden" id="collapse-icon-right" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
    </div>
    <nav class="flex-1 px-3 py-2 space-y-0.5 overflow-y-auto" aria-label="Main">
        @php $nav = [
            ['url' => '/dashboard/' . auth()->id(), 'match' => 'dashboard*', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1v-2zM14 13a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1h-4a1 1 0 01-1-1v-5z"/>', 'label' => 'Dashboard'],
            ['url' => '/beranda/' . auth()->id(), 'match' => 'beranda*', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>', 'label' => 'My Files'],
            ['url' => '#', 'match' => 'recent*', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>', 'label' => 'Recent'],
            ['url' => '#', 'match' => 'starred*', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>', 'label' => 'Starred'],
            ['url' => '#', 'match' => 'shared*', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>', 'label' => 'Shared'],
        ]; @endphp
        @foreach($nav as $n)
        <a href="{{ $n['url'] }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ Request::is($n['match']) ? 'bg-blue-600 text-white' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $n['icon'] !!}</svg>
            <span class="sidebar-label">{{ $n['label'] }}</span>
        </a>
        @endforeach
        <div class="!my-3 border-t border-gray-100"></div>
        <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 transition-all">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            <span class="sidebar-label">Trash</span>
        </a>
    </nav>
    {{-- Storage --}}
    <div class="px-3 py-4 border-t border-gray-100">
        <div id="storage-block" class="p-3 bg-gray-50 rounded-xl border border-gray-100">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-7 h-7 bg-white rounded-lg flex items-center justify-center border border-gray-200">
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                </div>
                <span class="sidebar-label text-xs font-semibold text-gray-800">Storage</span>
            </div>
            @php
                $pct = (auth()->user()->storage_used / auth()->user()->storage_quota) * 100;
                $uMB = number_format(auth()->user()->storage_used / 1048576, 1);
                $tMB = number_format(auth()->user()->storage_quota / 1048576, 0);
            @endphp
            <div class="h-1.5 w-full bg-gray-200 rounded-full overflow-hidden mb-2" role="progressbar" aria-valuenow="{{ round($pct) }}" aria-valuemin="0" aria-valuemax="100">
                <div class="h-full rounded-full transition-all {{ $pct > 90 ? 'bg-red-500' : ($pct > 70 ? 'bg-amber-500' : 'bg-blue-600') }}" style="width:{{ min($pct,100) }}%"></div>
            </div>
            <p class="sidebar-label text-xs text-gray-500"><span class="font-medium text-gray-800">{{ $uMB }} MB</span> of {{ $tMB }} MB</p>
        </div>
        <div id="storage-icon" class="hidden justify-center" title="{{ $uMB }}MB / {{ $tMB }}MB">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
        </div>
    </div>
</aside>
@endauth
<div id="sidebar-overlay" class="fixed inset-0 z-20 bg-black/20 hidden lg:hidden transition-opacity" aria-hidden="true"></div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- CONTEXT MENU (desktop right-click / kebab)             --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div id="context-menu" class="hidden fixed z-[80] min-w-[180px] py-1.5 bg-white rounded-xl border border-gray-200 shadow-lg" role="menu" aria-label="File actions">
    <button data-ctx="open"     class="w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-800" role="menuitem"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h5l2 2h5a2 2 0 012 2v8a2 2 0 01-2 2H5z"/></svg>Open</button>
    <button data-ctx="download" class="w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-800" role="menuitem"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>Download</button>
    <button data-ctx="rename"   class="w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-800" role="menuitem"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Rename</button>
    <button data-ctx="move"     class="w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-800" role="menuitem"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>Move</button>
    <button data-ctx="share"    class="w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-800" role="menuitem"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>Copy Link</button>
    <div class="my-1 border-t border-gray-100"></div>
    <button data-ctx="delete"   class="w-full flex items-center gap-3 px-3 py-2 text-sm text-red-500 hover:bg-red-50 hover:text-red-600" role="menuitem"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Delete</button>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- BOTTOM SHEET (mobile actions)                          --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div id="bottom-sheet" class="hidden fixed inset-0 z-[80]" aria-modal="true">
    <div id="bs-overlay" class="absolute inset-0 bg-black/20"></div>
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl border-t border-gray-200 shadow-lg max-h-[70vh] overflow-y-auto">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <span id="bs-title" class="text-sm font-semibold text-gray-800 truncate"></span>
            <button id="bs-close" class="p-1 text-gray-400 hover:text-gray-600 rounded" aria-label="Close"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div class="py-2">
            <button data-bs="open"     class="w-full flex items-center gap-4 px-5 py-3 text-sm text-gray-700 hover:bg-gray-50 active:bg-gray-100"><svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h5l2 2h5a2 2 0 012 2v8a2 2 0 01-2 2H5z"/></svg>Open</button>
            <button data-bs="download" class="w-full flex items-center gap-4 px-5 py-3 text-sm text-gray-700 hover:bg-gray-50 active:bg-gray-100"><svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>Download</button>
            <button data-bs="rename"   class="w-full flex items-center gap-4 px-5 py-3 text-sm text-gray-700 hover:bg-gray-50 active:bg-gray-100"><svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Rename</button>
            <button data-bs="move"     class="w-full flex items-center gap-4 px-5 py-3 text-sm text-gray-700 hover:bg-gray-50 active:bg-gray-100"><svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>Move</button>
            <button data-bs="share"    class="w-full flex items-center gap-4 px-5 py-3 text-sm text-gray-700 hover:bg-gray-50 active:bg-gray-100"><svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>Copy Link</button>
            <div class="my-1 border-t border-gray-100"></div>
            <button data-bs="delete"   class="w-full flex items-center gap-4 px-5 py-3 text-sm text-red-500 hover:bg-red-50 active:bg-red-100"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Delete</button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- CONFIRM MODAL                                          --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div id="confirm-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/20" role="dialog" aria-modal="true">
    <div class="bg-white rounded-2xl border border-gray-200 shadow-lg w-full max-w-sm mx-4 p-6">
        <div class="flex items-start gap-4 mb-5">
            <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
            <div>
                <h3 id="confirm-title" class="text-base font-semibold text-gray-800"></h3>
                <p id="confirm-message" class="text-sm text-gray-500 mt-1"></p>
            </div>
        </div>
        <div class="flex justify-end gap-3">
            <button id="confirm-cancel-btn" class="px-4 py-2 text-sm font-medium text-gray-500 hover:bg-gray-100 rounded-xl">Cancel</button>
            <button id="confirm-ok-btn" class="px-4 py-2 text-sm font-medium rounded-xl text-white bg-red-500 hover:bg-red-600"></button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- NEW FOLDER MODAL                                       --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div id="new-folder-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/20" onclick="if(event.target===this) this.classList.add('hidden')" role="dialog" aria-modal="true">
    <div class="bg-white rounded-2xl border border-gray-200 shadow-lg w-full max-w-md mx-4 p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-4">Create New Folder</h3>
        <form action="/folder" method="POST">@csrf
            <input type="hidden" name="parent_id" value="{{ $current_folder->id ?? $isi_folder->id ?? '' }}">
            <input type="text" name="nama" placeholder="Folder name…" required autofocus
                   class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/10 mb-4" aria-label="Folder name">
            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('new-folder-modal').classList.add('hidden')" class="px-4 py-2.5 text-sm font-medium text-gray-500 hover:bg-gray-100 rounded-xl">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl">Create</button>
            </div>
        </form>
    </div>
</div>

{{-- Mobile FAB --}}
@auth
<div class="fixed bottom-6 right-6 z-40 sm:hidden">
    <button id="mobile-fab" class="flex items-center justify-center w-14 h-14 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 active:scale-95 transition-all" aria-label="Upload">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    </button>
</div>
@endauth

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- MAIN CONTENT                                           --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<main id="main-content" class="lg:ml-60 pt-16 min-h-screen transition-all duration-300">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        @if(session('error'))
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600 flex items-center gap-3" role="alert">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('error') }}
        </div>
        @endif
        @if(session('status') || session('notif') || session('nama_tampil') || session('folder_status') || session('status_file'))
        <div class="mb-4 px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-600 flex items-center gap-3" role="alert">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('status') ?? session('notif') ?? (session('nama_tampil') ? 'File ' . session('nama_tampil') . ' added!' : '') ?? session('folder_status') ?? session('status_file') }}
        </div>
        @endif
        @yield('content')
    </div>
</main>

@stack('scripts')
</body>
</html>
