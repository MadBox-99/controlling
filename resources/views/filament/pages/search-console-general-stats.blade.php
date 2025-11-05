<x-filament-panels::page>
    @vite(['resources/js/app.js', 'resources/css/app.css'])

    <div class="flex flex-col gap-6">
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
                        LEKÉRDEZÉSEK
                    </button>
                    <button
                        @click="activeTab = 'pages'"
                        :class="activeTab === 'pages' ? 'border-primary-600 text-primary-600 dark:border-primary-500 dark:text-primary-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-300'"
                        class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors"
                    >
                        OLDALAK
                    </button>
                    <button
                        @click="activeTab = 'devices'"
                        :class="activeTab === 'devices' ? 'border-primary-600 text-primary-600 dark:border-primary-500 dark:text-primary-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-300'"
                        class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors"
                    >
                        ESZKÖZÖK
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
