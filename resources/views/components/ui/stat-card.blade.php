@props(['label', 'value', 'icon' => null, 'trend' => null])

<div {{ $attributes->merge(['class' => 'agro-card agro-card-body']) }}>
    <div class="flex items-start justify-between gap-2">
        @if ($icon)
            <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg bg-brand-600/10 px-2 text-xs font-bold leading-none text-brand-700 dark:text-brand-300" aria-hidden="true">{{ $icon }}</span>
        @endif
    </div>
    <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $label }}</p>
    <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $value }}</p>
    @if ($trend)
        <p class="mt-1 text-xs text-brand-600 dark:text-brand-400">{{ $trend }}</p>
    @endif
</div>
