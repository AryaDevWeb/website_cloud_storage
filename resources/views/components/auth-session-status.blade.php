@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-sky-600 dark:text-sky-400 bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800 rounded-xl p-3']) }}>
        {{ $status }}
    </div>
@endif
