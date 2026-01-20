<x-master-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">イベント管理</h2>
    </x-slot>

    <div class="py-12" x-data="{ tab: 'upcoming' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="flex space-x-4 mb-6 border-b border-gray-200 dark:border-gray-700">
                <button @click="tab = 'upcoming'" 
                    :class="tab === 'upcoming' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="py-2 px-4 border-b-2 font-medium text-sm transition">
                    開催中・予定 ({{ $upcomingEvents->count() }})
                </button>
                <button @click="tab = 'past'" 
                    :class="tab === 'past' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="py-2 px-4 border-b-2 font-medium text-sm transition">
                    過去のイベント ({{ $pastEvents->count() }})
                </button>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                
                <div x-show="tab === 'upcoming'">
                    @include('master.events.partials.table', ['events' => $upcomingEvents, 'type' => 'upcoming'])
                </div>

                <div x-show="tab === 'past'" x-cloak>
                    @include('master.events.partials.table', ['events' => $pastEvents, 'type' => 'past'])
                </div>

            </div>
        </div>
    </div>
</x-master-layout>