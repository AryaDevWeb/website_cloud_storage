@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800/90 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:border-sky-500 dark:focus:border-sky-400 focus:ring-sky-500 dark:focus:ring-sky-400 rounded-xl shadow-sm transition text-sm']) }}>
