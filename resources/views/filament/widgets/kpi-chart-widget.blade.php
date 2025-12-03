<x-filament-widgets::widget>

            @vite(['resources/css/app.css', 'resources/js/app.js'])

    <x-filament::section>
        <div class="space-y-4">
            <!-- KPI Selector -->
            <div>
                <select
                    wire:model.live="selectedKpiId"
                    class="w-full rounded-md border-2 border-blue-300 bg-blue-50 px-4 py-3 text-base font-medium text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-blue-600 dark:bg-blue-900/30 dark:text-white"
                >
                    <option value="">{{ __('Select KPI...') }}</option>
                    @foreach($this->getKpis() as $kpi)
                        <option value="{{ $kpi->id }}">{{ $kpi->name }}</option>
                    @endforeach
                </select>
            </div>

            @php
                $data = $this->getKpiData();
            @endphp

            @if($data && $data['kpi']->target_value)
                <!-- Circular Progress Chart -->
                <div class="flex items-center justify-center py-4">
                    <div class="relative" style="width: 180px; height: 180px;">
                        <svg class="absolute inset-0" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="40" fill="none" stroke="#d1d5db" stroke-width="10" class="dark:stroke-gray-600"/>
                            <circle
                                cx="50" cy="50" r="40" fill="none"
                                stroke="{{ $data['isTargetMet'] ? '#10b981' : '#3b82f6' }}"
                                stroke-width="10" stroke-dasharray="{{ $data['achievedPercentage'] * 2.51 }} 251"
                                stroke-linecap="round" transform="rotate(-90 50 50)" class="transition-all duration-500"
                            />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <div class="text-3xl font-bold {{ $data['isTargetMet'] ? 'text-green-600 dark:text-green-400' : 'text-blue-600 dark:text-blue-400' }}">
                                {{ $data['achievedPercentage'] }}%
                            </div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">{{ __('Achieved') }}</div>
                        </div>
                    </div>
                </div>

                <!-- KPI Details -->
                <div class="space-y-3">
                    <div class="rounded-lg bg-blue-100 p-3 dark:bg-blue-900/30">
                        <div class="text-xs font-semibold uppercase text-blue-900 dark:text-blue-300">{{ __('Current Value') }}</div>
                        <div class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($data['currentValue'], 2, ',', ' ') }}
                        </div>
                    </div>

                    <div class="rounded-lg bg-green-100 p-3 dark:bg-green-900/30">
                        <div class="text-xs font-semibold uppercase text-green-900 dark:text-green-300">{{ __('Target') }}</div>
                        <div class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($data['kpi']->target_value, 2, ',', ' ') }}
                        </div>
                    </div>

                    <div class="flex items-center justify-center rounded-lg border-2 p-3 {{ $data['isTargetMet'] ? 'border-green-600 bg-green-100 dark:border-green-700 dark:bg-green-900/30' : 'border-blue-600 bg-blue-100 dark:border-blue-700 dark:bg-blue-900/30' }}">
                        <span class="text-sm font-bold {{ $data['isTargetMet'] ? 'text-green-900 dark:text-green-200' : 'text-blue-900 dark:text-blue-200' }}">
                            {{ $data['isTargetMet'] ? '✓ ' . __('Target Met') : __('In Progress') }}
                        </span>
                    </div>
                </div>
            @elseif($data && !$data['kpi']->target_value)
                <!-- KPI without target - show only current value -->
                <div class="space-y-3">
                    <div class="rounded-lg bg-blue-100 p-4 dark:bg-blue-900/30">
                        <div class="text-xs font-semibold uppercase text-blue-900 dark:text-blue-300">{{ __('Current Value') }}</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($data['currentValue'], 2, ',', ' ') }}
                        </div>
                    </div>

                    <div class="rounded-lg border-2 border-yellow-400 bg-yellow-50 p-3 dark:border-yellow-600 dark:bg-yellow-900/20">
                        <div class="text-center text-sm font-semibold text-yellow-800 dark:text-yellow-200">
                            ⚠️ {{ __('No target value set') }}
                        </div>
                    </div>
                </div>
            @else
                <div class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    {{ __('No data available. Please select a KPI with configured analytics data.') }}
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
