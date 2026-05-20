@props(['variant' => 'default'])

@php
$classes = match ($variant) {
    'success' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300',
    'warning' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300',
    'danger' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300',
    'info' => 'bg-sky-100 text-sky-800 dark:bg-sky-900/50 dark:text-sky-300',
    default => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold {$classes}"]) }}>
    {{ $slot }}
</span>

