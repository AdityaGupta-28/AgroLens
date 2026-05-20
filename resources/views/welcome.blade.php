<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Land Insights Platform</title>
    @include('partials.theme-init')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="min-h-full bg-slate-100 font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
    <div class="flex min-h-full flex-col">
        <header class="border-b border-slate-200 bg-white/80 backdrop-blur-md dark:border-slate-800 dark:bg-slate-900/80">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-600/15 text-xl">🌾</div>
                    <div>
                        <p class="text-lg font-bold leading-tight text-slate-900 dark:text-white">{{ config('app.name') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">National Agricultural Intelligence</p>
                    </div>
                </div>
                <nav class="flex items-center gap-2 sm:gap-3">
                    <x-theme-toggle />
                    @auth
                        <a href="{{ route('dashboard') }}" class="agro-btn-primary">Open Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="agro-btn-secondary hidden sm:inline-flex">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="agro-btn-primary">Register</a>
                        @endif
                    @endauth
                </nav>
            </div>
        </header>

        <main class="flex flex-1 items-center">
            <div class="mx-auto grid max-w-6xl items-center gap-12 px-6 py-16 lg:grid-cols-2">
                <div>
                    <p class="mb-3 text-sm font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-400">Government & Research Ready</p>
                    <h1 class="mb-6 text-4xl font-bold leading-tight text-slate-900 dark:text-white lg:text-5xl">
                        Land Insights: Holding, Irrigation & Cropping Patterns
                    </h1>
                    <p class="mb-8 text-lg leading-relaxed text-slate-600 dark:text-slate-400">
                        Analyze regional agriculture across India — land holdings, well depth, irrigation sources,
                        crop diversity, and GIS maps for policymakers and field officers.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="agro-btn-primary px-6 py-3">Go to Analytics Dashboard →</a>
                        @else
                            <a href="{{ route('login') }}" class="agro-btn-primary px-6 py-3">Log in to Dashboard →</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="agro-btn-secondary px-6 py-3">Create Account</a>
                            @endif
                        @endauth
                    </div>

                </div>

                <div class="grid grid-cols-2 gap-4">
                    @foreach ([
                        ['icon' => '📊', 'title' => 'Live Analytics', 'desc' => 'KPIs, charts & filters'],
                        ['icon' => '🗺️', 'title' => 'GIS Maps', 'desc' => 'District-level markers'],
                        ['icon' => '💧', 'title' => 'Irrigation', 'desc' => 'Sources & water stress'],
                        ['icon' => '🌱', 'title' => 'Crop Patterns', 'desc' => 'Kharif, Rabi, Zaid data'],
                    ] as $feature)
                        <div class="agro-card agro-card-body transition hover:border-brand-500/30">
                            <div class="mb-2 text-2xl">{{ $feature['icon'] }}</div>
                            <h3 class="font-semibold text-slate-900 dark:text-white">{{ $feature['title'] }}</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $feature['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </main>

        <footer class="border-t border-slate-200 py-6 text-center text-sm text-slate-500 dark:border-slate-800 dark:text-slate-500">
            {{ config('app.name') }} · Laravel {{ Illuminate\Foundation\Application::VERSION }}
        </footer>
    </div>
</body>
</html>
