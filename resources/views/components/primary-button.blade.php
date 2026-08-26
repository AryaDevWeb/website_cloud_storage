<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-sky-500 hover:bg-sky-600 active:bg-sky-700 dark:bg-sky-500 dark:hover:bg-sky-400 border border-transparent rounded-xl font-semibold text-sm text-white dark:text-zinc-950 shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-400 focus:ring-offset-2 dark:focus:ring-offset-zinc-900 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
