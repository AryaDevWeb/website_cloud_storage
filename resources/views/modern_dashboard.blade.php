@extends('layouts.modern')

@section('title', 'Modern Dashboard')

@section('content')

    {{-- HEADER STATS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white p-6 rounded-3xl border border-[#f1f5f9] shadow-soft flex items-center gap-5">
            <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-[#94a3b8] uppercase tracking-wider mb-1">Total Files</p>
                <p class="text-2xl font-bold text-[#0f172a]">1,284</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-[#f1f5f9] shadow-soft flex items-center gap-5">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-[#94a3b8] uppercase tracking-wider mb-1">Folders</p>
                <p class="text-2xl font-bold text-[#0f172a]">42</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-[#f1f5f9] shadow-soft flex items-center gap-5">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-[#94a3b8] uppercase tracking-wider mb-1">Shared with me</p>
                <p class="text-2xl font-bold text-[#0f172a]">12</p>
            </div>
        </div>
    </div>

    {{-- QUICK FOLDERS --}}
    <div class="mb-10">
        <div class="flex items-center justify-between mb-6 px-1">
            <h2 class="text-xl font-bold text-[#0f172a]">Quick Folders</h2>
            <button class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 transition-colors">View All</button>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $folders = [
                    ['name' => 'Design Assets', 'count' => '124 files', 'color' => 'bg-indigo-500'],
                    ['name' => 'Development', 'count' => '412 files', 'color' => 'bg-emerald-500'],
                    ['name' => 'Marketing', 'count' => '86 files', 'color' => 'bg-amber-500'],
                    ['name' => 'Documents', 'count' => '232 files', 'color' => 'bg-rose-500'],
                ];
            @endphp
            @foreach($folders as $folder)
            <div class="bg-white p-5 rounded-3xl border border-[#f1f5f9] shadow-soft hover:shadow-lg hover:-translate-y-1 transition-all cursor-pointer group">
                <div class="w-10 h-10 {{ $folder['color'] }} rounded-2xl mb-4 flex items-center justify-center text-white shadow-lg">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                        <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-[#0f172a] mb-1 group-hover:text-indigo-600 transition-colors">{{ $folder['name'] }}</h3>
                <p class="text-xs text-[#94a3b8] font-medium">{{ $folder['count'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- RECENT FILES TABLE --}}
    <div class="bg-white rounded-[2.5rem] border border-[#f1f5f9] shadow-soft overflow-hidden">
        <div class="p-8 border-b border-[#f1f5f9] flex items-center justify-between">
            <h2 class="text-xl font-bold text-[#0f172a]">Recent Files</h2>
            <div class="flex items-center gap-2">
                <button class="p-2 text-[#94a3b8] hover:bg-[#f8fafc] hover:text-[#0f172a] rounded-xl transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <button class="p-2 text-indigo-600 bg-indigo-50 rounded-xl transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[#94a3b8] text-xs font-bold uppercase tracking-widest border-b border-[#f1f5f9]">
                        <th class="px-8 py-5">File Name</th>
                        <th class="px-8 py-5">Owner</th>
                        <th class="px-8 py-5">Size</th>
                        <th class="px-8 py-5 text-right">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f1f5f9]">
                    @php
                        $files = [
                            ['name' => 'Project_Presentation.pptx', 'type' => 'Pres', 'owner' => 'Alex Kim', 'size' => '12.4 MB', 'date' => 'Today, 10:45 AM', 'bg' => 'bg-orange-50', 'text' => 'text-orange-600'],
                            ['name' => 'Website_Assets.zip', 'type' => 'Arch', 'owner' => 'John Doe', 'size' => '856.2 MB', 'date' => 'Yesterday, 4:20 PM', 'bg' => 'bg-indigo-50', 'text' => 'text-indigo-600'],
                            ['name' => 'Monthly_Report.pdf', 'type' => 'Doc', 'owner' => 'Sarah Smith', 'size' => '2.1 MB', 'date' => 'Mar 8, 2026', 'bg' => 'bg-rose-50', 'text' => 'text-rose-600'],
                            ['name' => 'Interface_Mockup.fig', 'type' => 'Fig', 'owner' => 'Sarah Smith', 'size' => '45.8 MB', 'date' => 'Mar 5, 2026', 'bg' => 'bg-blue-50', 'text' => 'text-blue-600'],
                        ];
                    @endphp
                    @foreach($files as $file)
                    <tr class="hover:bg-[#f8fafc] transition-colors cursor-pointer group">
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 {{ $file['bg'] }} {{ $file['text'] }} rounded-xl flex items-center justify-center font-bold text-[10px]">{{ $file['type'] }}</div>
                                <span class="font-bold text-[#1e293b] group-hover:text-indigo-600 transition-colors">{{ $file['name'] }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-5 text-[#64748b] text-sm font-medium">{{ $file['owner'] }}</td>
                        <td class="px-8 py-5 text-[#64748b] text-sm font-medium">{{ $file['size'] }}</td>
                        <td class="px-8 py-5 text-right text-[#64748b] text-sm font-medium">{{ $file['date'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
