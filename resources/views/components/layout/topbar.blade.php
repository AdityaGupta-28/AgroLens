<header class="sticky top-0 z-20 flex h-16 shrink-0 items-center gap-4 border-b border-slate-200 bg-white/90 px-4 backdrop-blur-md dark:border-slate-800 dark:bg-slate-900/90 sm:px-6 lg:px-8">
    <button
        type="button"
        @click="sidebarOpen = !sidebarOpen"
        class="inline-flex items-center justify-center rounded-xl border border-slate-200 p-2 text-slate-600 hover:bg-slate-50 lg:hidden dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
        aria-label="Toggle menu"
    >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div class="min-w-0 flex-1">
        @if (isset($header))
            {{ $header }}
        @endif
    </div>

    <div class="flex items-center gap-2 sm:gap-3">
        <x-theme-toggle />

        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                    <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-600/15 text-sm font-bold text-brand-700 dark:text-brand-300">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </span>
                </button>
            </x-slot>
            <x-slot name="content">
                <x-dropdown-link :href="route('profile')" wire:navigate>{{ __('Profile') }}</x-dropdown-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-start">
                        <x-dropdown-link>{{ __('Log Out') }}</x-dropdown-link>
                    </button>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</header>
