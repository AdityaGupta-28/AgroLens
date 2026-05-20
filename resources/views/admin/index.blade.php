<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-slate-900 dark:text-white sm:text-xl">{{ __('System Administration') }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Oversee security profiles, user credentials, audit trails, and platform statistics.') }}</p>
        </div>
    </x-slot>

    <!-- Top Stats Cards -->
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 mb-6">
        <x-ui.stat-card label="Total Users" :value="number_format($stats['users'])" icon="👥" />
        <x-ui.stat-card label="Super Admins" :value="number_format($stats['superadmins'])" icon="👑" />
        <x-ui.stat-card label="Gov Officers" :value="number_format($stats['officers'])" icon="🏛️" />
        <x-ui.stat-card label="Public Viewers" :value="number_format($stats['viewers'])" icon="👁️" />
    </div>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 mb-8">
        <x-ui.stat-card label="Farmers (DB)" :value="number_format($stats['farmers'])" icon="👨‍🌾" />
        <x-ui.stat-card label="Districts" :value="number_format($stats['districts'])" icon="🗺️" />
        <x-ui.stat-card label="Land Holdings" :value="number_format($stats['holdings'])" icon="🌾" />
        <x-ui.stat-card label="Active Surveys" :value="number_format($stats['surveys'])" icon="📋" />
    </div>

    <!-- User Management Section -->
    <div class="mb-8">
        <div class="border-b border-slate-200/80 pb-4 mb-6 dark:border-slate-700/80">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">User Accounts & Access Control</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Manage security credentials, assign organizational roles, and monitor account lifecycle status.</p>
        </div>
        <livewire:admin.user-management />
    </div>

    <!-- Audits & Policies Grid -->
    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Role Security Card (1/3 width) -->
        <div class="lg:col-span-1">
            <x-ui.card title="Security Policy Overview" description="Access privileges mapped per system role.">
                <ul class="space-y-4 text-sm text-slate-600 dark:text-slate-300">
                    <li class="flex items-start gap-3">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-red-500 animate-pulse"></span>
                        <span><strong class="text-slate-900 dark:text-white">Super Admin</strong> — Full dashboard access, region management, developer API keys, survey administration, and full user account management.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                        <span><strong class="text-slate-900 dark:text-white">Government Officer</strong> — Read-write access to dashboards, GIS maps, planning tools, and primary survey collection tools. Cannot manage users.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-slate-400"></span>
                        <span><strong class="text-slate-900 dark:text-white">Public Viewer</strong> — Read-only access to GIS maps and regional dashboard analytics. No write operations.</span>
                    </li>
                </ul>
            </x-ui.card>
        </div>

        <!-- Activity & Audit Logs Card (2/3 width) -->
        <div class="lg:col-span-2">
            <x-ui.card title="System Audit Logs" description="Real-time capture of events and operations executed across the AgroLens platform." :padding="false">
                <div class="overflow-x-auto">
                    <table class="agro-table w-full">
                        <thead>
                            <tr>
                                <th>Identity</th>
                                <th>Action</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($recentActivity as $log)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">
                                         {{ $log->user?->name ?? 'System' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-600 dark:text-slate-400">
                                         {{ str_replace('.', ' ', ucfirst($log->action)) }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500 dark:text-slate-400">
                                         {{ $log->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8">
                                        <x-ui.empty-state title="No activity recorded" description="All system actions will appear here in real-time." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
