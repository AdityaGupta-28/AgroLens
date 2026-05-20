@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'mb-6']) }}>
    <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-2xl">{{ $title }}</h1>
    @if ($description)
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
    @endif
</div>
