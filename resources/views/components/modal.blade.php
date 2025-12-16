@props([
    'title' => '確認',
    'confirmText' => 'OK',
    'confirmColor' => 'bg-blue-600',
    'confirmAction' => null
])

<div x-data="{ open: false }" class="relative">

    {{-- trigger は x-data の中でそのまま描画 --}}
    <div>
        {{ $trigger }}
    </div>

    <template x-if="open">
        <div class="fixed inset-0 flex justify-center items-center bg-black bg-opacity-40 z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-80">
                <h3 class="text-lg font-semibold mb-4 text-center">{{ $title }}</h3>

                <div class="text-gray-700 mb-4">
                    {{ $slot }}
                </div>

                <div class="flex justify-between gap-2">
                    <button
                        type="button"
                        @click="open = false"
                        class="w-1/2 bg-gray-300 text-gray-800 px-3 py-2 rounded hover:bg-gray-400 transition"
                    >
                        戻る
                    </button>

                    @if($confirmAction)
                        <form action="{{ $confirmAction }}" method="POST" class="w-1/2">
                            @csrf
                            @method('PATCH')
                            <button
                                type="submit"
                                class="w-full {{ $confirmColor }} text-white px-3 py-2 rounded hover:opacity-90 transition"
                            >
                                {{ $confirmText }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </template>
</div>
