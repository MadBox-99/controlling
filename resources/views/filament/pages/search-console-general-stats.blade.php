<x-filament-panels::page>
    @vite(['resources/js/app.js', 'resources/css/app.css'])

    <div class="flex flex-col gap-6">
        {{-- Date Range Filter --}}
        <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Period') }}:</span>
            <div class="inline-flex rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden shadow-sm">
                <button
                    wire:click="setDateRange('24_hours')"
                    class="{{ $dateRangeType === '24_hours' ? 'bg-amber-600 text-white hover:bg-amber-700 dark:bg-amber-500 dark:text-gray-900' : 'bg-gray-100 text-gray-900 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }} px-4 py-2 text-sm font-medium transition-colors border-r border-gray-300 dark:border-gray-600"
                >
                    {{ __('24 hours') }}
                </button>
                <button
                    wire:click="setDateRange('7_days')"
                    class="{{ $dateRangeType === '7_days' ? 'bg-amber-600 text-white hover:bg-amber-700 dark:bg-amber-500 dark:text-gray-900' : 'bg-gray-100 text-gray-900 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }} px-4 py-2 text-sm font-medium transition-colors border-r border-gray-300 dark:border-gray-600"
                >
                    {{ __('7 days') }}
                </button>
                <button
                    wire:click="setDateRange('28_days')"
                    class="{{ $dateRangeType === '28_days' ? 'bg-amber-600 text-white hover:bg-amber-700 dark:bg-amber-500 dark:text-gray-900' : 'bg-gray-100 text-gray-900 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }} px-4 py-2 text-sm font-medium transition-colors border-r border-gray-300 dark:border-gray-600"
                >
                    {{ __('28 days') }}
                </button>
                <button
                    wire:click="setDateRange('3_months')"
                    class="{{ $dateRangeType === '3_months' ? 'bg-amber-600 text-white hover:bg-amber-700 dark:bg-amber-500 dark:text-gray-900' : 'bg-gray-100 text-gray-900 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }} px-4 py-2 text-sm font-medium transition-colors"
                >
                    {{ __('3 months') }}
                </button>
            </div>
        </div>

        {{-- Performance Chart --}}
        <div>
            @livewire(\App\Filament\Widgets\SearchConsoleChart::class)
        </div>

        {{-- Tabs Navigation --}}
        <div x-data="{ activeTab: 'queries' }" class="flex flex-col gap-6">
            {{-- Tab Headers --}}
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex gap-6" aria-label="Tabs">
                    <button
                        @click="activeTab = 'queries'"
                        :class="activeTab === 'queries' ? 'border-primary-600 text-primary-600 dark:border-primary-500 dark:text-primary-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-300'"
                        class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors"
                    >
                        {{ __('Queries') }}
                    </button>
                    <button
                        @click="activeTab = 'pages'"
                        :class="activeTab === 'pages' ? 'border-primary-600 text-primary-600 dark:border-primary-500 dark:text-primary-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-300'"
                        class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors"
                    >
                        {{ __('Pages') }}
                    </button>
                    <button
                        @click="activeTab = 'devices'"
                        :class="activeTab === 'devices' ? 'border-primary-600 text-primary-600 dark:border-primary-500 dark:text-primary-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-300'"
                        class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors"
                    >
                        {{ __('Devices') }}
                    </button>
                </nav>
            </div>

            {{-- Tab Contents --}}
            <div>
                {{-- Queries Tab --}}
                <div x-show="activeTab === 'queries'" x-transition>
                    @livewire(\App\Filament\Widgets\TopSearchQueriesTable::class)
                </div>

                {{-- Pages Tab --}}
                <div x-show="activeTab === 'pages'" x-cloak x-transition>
                    @livewire(\App\Filament\Widgets\TopSearchPagesTable::class)
                </div>

                {{-- Devices Tab --}}
                <div x-show="activeTab === 'devices'" x-cloak x-transition>
                    @livewire(\App\Filament\Widgets\DeviceBreakdownTable::class)
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
