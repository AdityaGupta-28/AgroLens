<div>
    @if (session()->has('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 p-4 border border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800/30">
            <div class="flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">✓</span>
                <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- Controls Bar -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
            <!-- Search Input -->
            <div class="relative max-w-md flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search users by name or email..."
                    class="agro-input pl-10 h-11"
                />
            </div>

            <!-- Role Filter -->
            <div class="w-full sm:w-48">
                <select wire:model.live="roleFilter" class="agro-select h-11">
                    <option value="">All Roles</option>
                    @foreach ($roles as $roleOption)
                        <option value="{{ $roleOption->value }}">{{ $roleOption->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Add User Button -->
        <div>
            <button wire:click="openCreateForm" class="agro-btn-primary h-11 px-5 w-full sm:w-auto shadow-sm flex items-center justify-center gap-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Add User Account
            </button>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="agro-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="agro-table w-full">
                <thead>
                    <tr>
                        <th>User Profile</th>
                        <th>Email Address</th>
                        <th>Security Role</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="whitespace-nowrap px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <!-- Initials Avatar -->
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-tr from-brand-600 to-emerald-500 font-bold text-white shadow-sm">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900 dark:text-white">{{ $user->name }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">ID: #{{ $user->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-slate-600 dark:text-slate-300">
                                {{ $user->email }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-4">
                                @php
                                    $roleEnum = $user->role;
                                    $roleVal = $roleEnum instanceof \App\Enums\UserRole ? $roleEnum->value : $roleEnum;
                                    $badgeVariant = match($roleVal) {
                                        'super_admin' => 'danger',
                                        'government_officer' => 'success',
                                        default => 'default'
                                    };
                                    $label = $roleEnum instanceof \App\Enums\UserRole ? $roleEnum->label() : ucwords(str_replace('_', ' ', $roleVal));
                                @endphp
                                <x-ui.badge :variant="$badgeVariant">
                                    {{ $label }}
                                </x-ui.badge>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4">
                                <button
                                    wire:click="toggleStatus({{ $user->id }})"
                                    class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold transition-colors {{ $user->is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}"
                                    title="Click to toggle status"
                                    @if ($user->id === auth()->id()) disabled @endif
                                >
                                    <span class="h-1.5 w-1.5 rounded-full {{ $user->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Edit Button -->
                                    <button
                                        wire:click="openEditForm({{ $user->id }})"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                                        title="Edit Account"
                                    >
                                        <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>

                                    <!-- Delete Button -->
                                    @if ($user->id !== auth()->id())
                                        @if ($confirmingDeletion === $user->id)
                                            <button
                                                wire:click="deleteUser"
                                                class="inline-flex h-9 px-3 items-center justify-center rounded-lg bg-red-600 text-xs font-bold text-white transition hover:bg-red-700 shadow-sm"
                                            >
                                                Confirm?
                                            </button>
                                            <button
                                                wire:click="$set('confirmingDeletion', null)"
                                                class="inline-flex h-9 px-3 items-center justify-center rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                            >
                                                Cancel
                                            </button>
                                        @else
                                            <button
                                                wire:click="confirmDelete({{ $user->id }})"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 transition hover:bg-red-100 dark:border-red-900/30 dark:bg-red-950/20 dark:text-red-400 dark:hover:bg-red-950/40"
                                                title="Delete Account"
                                            >
                                                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8">
                                <div class="text-center">
                                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-2xl dark:bg-slate-800">🔍</span>
                                    <h3 class="mt-4 text-sm font-semibold text-slate-900 dark:text-white">No accounts found</h3>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Try adjusting your keywords or role filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="border-t border-slate-100 bg-slate-50/50 px-5 py-4 dark:border-slate-800 dark:bg-slate-900/50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- User Account Form Modal Panel -->
    @if ($showForm)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" wire:click="resetForm"></div>

                <!-- Modal Positioning Trick -->
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                <!-- Modal Panel Card -->
                <div class="inline-block transform overflow-hidden rounded-2xl bg-white text-left align-bottom shadow-xl transition-all dark:bg-slate-900 sm:my-8 sm:w-full sm:max-w-lg sm:align-middle border border-slate-100 dark:border-slate-800">
                    <div class="bg-gradient-to-r from-brand-700 to-brand-900 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white" id="modal-title">
                                {{ $isEdit ? 'Modify Account Details' : 'Register New User Account' }}
                            </h3>
                            <button wire:click="resetForm" class="rounded-lg p-1 text-brand-200 hover:bg-brand-800/40 hover:text-white transition-colors">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs text-brand-200 mt-1">Configure credentials and regional roles below.</p>
                    </div>

                    <form wire:submit.prevent="saveUser">
                        <div class="px-6 py-5 space-y-4">
                            <!-- Name -->
                            <div>
                                <label class="agro-label">Full Name</label>
                                <input type="text" wire:model="name" class="agro-input" placeholder="e.g. Aditya Gupta" required />
                                @error('name') <span class="text-xs font-semibold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="agro-label">Email Address</label>
                                <input type="email" wire:model="email" class="agro-input" placeholder="e.g. user@agrolens.gov.in" required />
                                @error('email') <span class="text-xs font-semibold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Password -->
                            <div>
                                <label class="agro-label">
                                    Password
                                    @if ($isEdit)
                                        <span class="text-xs font-normal text-slate-500">(Leave blank to keep current password)</span>
                                    @endif
                                </label>
                                <input type="password" wire:model="password" class="agro-input" placeholder="••••••••" @if (!$isEdit) required @endif />
                                @error('password') <span class="text-xs font-semibold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Grid (Role & Active) -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <!-- Role Selection -->
                                <div>
                                    <label class="agro-label">Security Role</label>
                                    <select wire:model="role" class="agro-select">
                                        @foreach ($roles as $roleOption)
                                            <option value="{{ $roleOption->value }}">{{ $roleOption->label() }}</option>
                                        @endforeach
                                    </select>
                                    @error('role') <span class="text-xs font-semibold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- Is Active Switch -->
                                <div>
                                    <label class="agro-label">Account Status</label>
                                    <div class="mt-2.5 flex items-center">
                                        <label class="relative inline-flex cursor-pointer items-center">
                                            <input
                                                type="checkbox"
                                                wire:model="is_active"
                                                class="peer sr-only"
                                                @if ($userId === auth()->id()) disabled @endif
                                            >
                                            <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-brand-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none dark:bg-slate-700"></div>
                                            <span class="ml-3 text-sm font-medium text-slate-600 dark:text-slate-400">
                                                {{ $is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </label>
                                    </div>
                                    @error('is_active') <span class="text-xs font-semibold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="bg-slate-50 dark:bg-slate-800/50 px-6 py-4 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end sm:gap-2">
                            <button
                                type="button"
                                wire:click="resetForm"
                                class="agro-btn-secondary h-10 px-4"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="agro-btn-primary h-10 px-5 shadow-sm"
                            >
                                {{ $isEdit ? 'Save Changes' : 'Register User' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
