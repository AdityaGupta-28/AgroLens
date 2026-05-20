@props(['type' => 'success'])

@php
$styles = match ($type) {
    'success' => 'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200',
    'warning' => 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-200',
    'error' => 'border-red-200 bg-red-50 text-red-900 dark:border-red-800 dark:bg-red-950/40 dark:text-red-200',
    'info' => 'border-sky-200 bg-sky-50 text-sky-900 dark:border-sky-800 dark:bg-sky-950/40 dark:text-sky-200',
    default => 'border-slate-200 bg-slate-50 text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200',
};
@endphp

<div {{ $attributes->merge(['class' => "rounded-xl border px-4 py-3 text-sm {$styles}"]) }} role="alert">
    {{ $slot }}
</div>

