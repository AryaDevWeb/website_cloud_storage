<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    <!-- Mobile Backdrop -->
    <div
        x-show="mobileSidebarOpen"
        x-cloak
        @click="mobileSidebarOpen = false"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 bg-zinc-950/60 backdrop-blur-xs md:hidden"
    ></div>

    <!-- Sidebar with Reduced Rounded Right Edge -->
    <aside
        @click.outside="sidebarOpen = false; mobileSidebarOpen = false"
        :class="{
            'translate-x-0': mobileSidebarOpen,
            '-translate-x-full': !mobileSidebarOpen,
            'md:translate-x-0': sidebarOpen,
            'md:-translate-x-full': !sidebarOpen
        }"
        class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-white dark:bg-zinc-900 border-r border-zinc-200/80 dark:border-zinc-800 rounded-r-xl shadow-lg transition-transform duration-300 ease-in-out overflow-hidden"
    >
        <!-- Sidebar Branding: Blue Background (No line separator) -->
        <div class="bg-gradient-to-br from-sky-500 via-sky-500 to-sky-600 dark:from-sky-600 dark:to-sky-700 text-white px-5 py-4 rounded-br-lg shadow-xs flex items-center justify-between">
            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3 group min-w-0">
                <img src="{{ asset('images/FLASK-logo-white.png') }}" alt="FLASK Logo" class="h-10 w-auto object-contain transition-transform group-hover:scale-105 shrink-0" />
                <div class="flex flex-col min-w-0">
                    <span class="text-sm font-bold text-white tracking-tight truncate">SMKN 1 Lumajang</span>
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-sky-100">Cloud Storage</span>
                </div>
            </a>

            <!-- Close button for both Desktop and Mobile -->
            <button
                @click="sidebarOpen = false; mobileSidebarOpen = false"
                class="rounded-lg p-1.5 text-sky-100 hover:text-white hover:bg-sky-600/60 active:bg-sky-700 transition cursor-pointer shrink-0"
                title="Tutup sidebar"
                aria-label="Tutup sidebar"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                </svg>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-3.5 py-4 space-y-1.5 overflow-y-auto">
            <!-- Ringkasan / Dashboard -->
            <a
                href="{{ route('dashboard') }}"
                wire:navigate
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-sky-500 text-white font-semibold shadow-sm shadow-sky-500/25' : 'text-zinc-600 hover:bg-sky-50 hover:text-sky-700 dark:text-zinc-400 dark:hover:bg-zinc-800/80 dark:hover:text-zinc-100' }}"
            >
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Ringkasan</span>
            </a>

            <!-- File Saya -->
            <a
                href="{{ \App\Filament\Pages\StorageWorkspace::getUrl([], true, 'workspace') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->is('workspace/storage-workspace') && !request()->query('section') ? 'bg-sky-500 text-white font-semibold shadow-sm shadow-sky-500/25' : 'text-zinc-600 hover:bg-sky-50 hover:text-sky-700 dark:text-zinc-400 dark:hover:bg-zinc-800/80 dark:hover:text-zinc-100' }}"
            >
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
                <span>File Saya</span>
            </a>

            <!-- Dibagikan -->
            <a
                href="{{ \App\Filament\Pages\StorageWorkspace::getUrl(['section' => 'shared'], true, 'workspace') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->is('workspace/storage-workspace') && request()->query('section') === 'shared' ? 'bg-sky-500 text-white font-semibold shadow-sm shadow-sky-500/25' : 'text-zinc-600 hover:bg-sky-50 hover:text-sky-700 dark:text-zinc-400 dark:hover:bg-zinc-800/80 dark:hover:text-zinc-100' }}"
            >
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                </svg>
                <span>Dibagikan</span>
            </a>

            <!-- Recycle Bin -->
            <a
                href="{{ \App\Filament\Pages\StorageWorkspace::getUrl(['section' => 'trash'], true, 'workspace') }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->is('workspace/storage-workspace') && request()->query('section') === 'trash' ? 'bg-sky-500 text-white font-semibold shadow-sm shadow-sky-500/25' : 'text-zinc-600 hover:bg-sky-50 hover:text-sky-700 dark:text-zinc-400 dark:hover:bg-zinc-800/80 dark:hover:text-zinc-100' }}"
            >
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-8 0h10" />
                </svg>
                <span>Recycle Bin</span>
            </a>
        </nav>

        <!-- Sidebar Footer -->
        <div class="p-4">
            <div class="rounded-xl bg-sky-50/60 dark:bg-zinc-800/60 border border-sky-100/60 dark:border-zinc-800 p-3 text-center">
                <p class="text-xs font-bold text-sky-700 dark:text-sky-400">SMKN 1 Lumajang</p>
                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">Platform Storage Sekolah</p>
            </div>
        </div>
    </aside>

    <!-- Top Bar Header: Soft Gradient with Subtle Reduced Curved Bottom -->
    <header
        :class="sidebarOpen ? 'md:pl-64' : 'md:pl-0'"
        class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-sky-100/70 dark:border-zinc-800/80 bg-gradient-to-r from-white via-sky-50/40 to-white dark:from-zinc-900 dark:via-sky-950/20 dark:to-zinc-900 px-4 sm:px-6 lg:px-8 rounded-b-xl shadow-xs backdrop-blur-md transition-all duration-300 ease-in-out"
    >
        <!-- Left: Burger bar button -->
        <div class="flex items-center gap-3">
            <button
                @click.stop="if (window.innerWidth < 768) { mobileSidebarOpen = !mobileSidebarOpen } else { sidebarOpen = !sidebarOpen }"
                type="button"
                class="inline-flex items-center justify-center rounded-xl p-2.5 text-zinc-600 hover:bg-sky-100/70 hover:text-sky-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200 transition focus:outline-none focus:ring-2 focus:ring-sky-400 cursor-pointer"
                aria-label="Buka atau tutup sidebar"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        <!-- Right: Account Dropdown -->
        <div class="flex items-center gap-3">
            <x-dropdown align="right" width="56">
                <x-slot name="trigger">
                    <button class="flex items-center gap-3 rounded-xl px-2.5 py-1.5 text-left transition hover:bg-sky-50/80 dark:hover:bg-zinc-800 cursor-pointer">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-sky-500 text-sm font-bold text-white shadow-xs">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <span class="hidden sm:block">
                            <span class="block text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ auth()->user()->name }}</span>
                            <span class="block text-xs text-zinc-500 dark:text-zinc-400">{{ auth()->user()->getRoleNames()->first() ?? 'User' }}</span>
                        </span>
                        <svg class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 20 20">
                            <path fill="currentColor" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01.02-1.06z" />
                        </svg>
                    </button>
                </x-slot>
                <x-slot name="content">
                    <x-dropdown-link :href="route('profile')" wire:navigate>Profil</x-dropdown-link>
                    <button wire:click="logout" class="w-full text-start"><x-dropdown-link>Keluar</x-dropdown-link></button>
                </x-slot>
            </x-dropdown>
        </div>
    </header>
</div>
