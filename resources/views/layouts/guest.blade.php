<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name') }}</title>
        @include('partials.theme-init')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>[x-cloak] { display: none !important; }</style>
    </head>
    <body class="min-h-full bg-slate-100 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <div class="relative flex min-h-full flex-col items-center justify-center px-4 py-12 sm:px-6">
            <div class="absolute right-4 top-4 sm:right-6 sm:top-6">
                <x-theme-toggle />
            </div>

            <a href="{{ route('home') }}" wire:navigate class="mb-8 flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-600/15 text-2xl">🌾</div>
                <div>
                    <p class="text-lg font-bold text-slate-900 dark:text-white">{{ config('app.name') }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Land Insights Platform</p>
                </div>
            </a>

            <div class="w-full max-w-md agro-card agro-card-body shadow-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
