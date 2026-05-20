@props(['title' => null, 'description' => null, 'padding' => true])

<div {{ $attributes->merge(['class' => 'agro-card']) }}>
    @if ($title || $description)
        <div class="border-b border-slate-200/80 px-5 py-4 dark:border-slate-700/80 sm:px-6">
            @if ($title)
                <h3 class="text-base font-semibold text-slate-900 dark:text-white">{{ $title }}</h3>
            @endif
            @if ($description)
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
            @endif
        </div>
    @endif
    <div @class(['agro-card-body' => $padding])>
        {{ $slot }}
    </div>
</div>

