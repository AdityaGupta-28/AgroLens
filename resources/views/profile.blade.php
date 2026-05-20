<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-slate-900 dark:text-white sm:text-xl">{{ __('Profile') }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Manage your account settings') }}</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-6">
        <x-ui.card>
            <livewire:profile.update-profile-information-form />
        </x-ui.card>

        <x-ui.card>
            <livewire:profile.update-password-form />
        </x-ui.card>

        <x-ui.card>
            <livewire:profile.delete-user-form />
        </x-ui.card>
    </div>
</x-app-layout>
