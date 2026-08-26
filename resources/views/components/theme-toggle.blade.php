<button
    x-data="{
        darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
        toggle() {
            this.darkMode = !this.darkMode;
            if (this.darkMode) {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            }
        }
    }"
    @click="toggle()"
    type="button"
    {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-xl p-2 text-zinc-500 hover:bg-sky-50 hover:text-sky-600 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-amber-400 transition focus:outline-none focus:ring-2 focus:ring-sky-400 cursor-pointer']) }}
    :title="darkMode ? 'Beralih ke mode terang' : 'Beralih ke mode gelap'"
    aria-label="Toggle dark mode"
>
    <!-- Sun icon (shown when in dark mode) -->
    <svg x-show="darkMode" x-cloak class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>
    <!-- Moon icon (shown when in light mode) -->
    <svg x-show="!darkMode" class="h-5 w-5 text-zinc-600 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
    </svg>
</button>