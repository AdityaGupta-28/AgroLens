<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name') }}</title>
        @include('partials.theme-init')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        <style>[x-cloak] { display: none !important; }</style>
    </head>
    <body class="h-full bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
        <div class="min-h-full" x-data="{ sidebarOpen: false }">
            <x-layout.sidebar />

            <div class="lg:pl-64">
                <x-layout.topbar>
                    @isset($header)
                        <x-slot name="header">{{ $header }}</x-slot>
                    @endisset
                </x-layout.topbar>

                @if (session('success'))
                    <div class="agro-page px-4 pt-4 sm:px-6 lg:px-8">
                        <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
                    </div>
                @endif

                <main class="agro-page px-4 py-6 sm:px-6 lg:px-8">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScripts
        @stack('scripts')
    </body>
</html>
