@props([
    'title' => '確認',
    'confirmText' => 'OK',
    'confirmColor' => 'bg-blue-600',
    'confirmAction' => null,
])

<div x-data="{ open: false }" class="relative">

    {{-- trigger --}}
    <div>
        {{ $trigger }}
    </div>

    <template x-if="open">
        <div class="fixed inset-0 flex justify-center items-center bg-black bg-opacity-40 z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-80">

                <h3 class="text-lg font-semibold mb-4 text-center">
                    {{ $title }}
                </h3>

                @if($confirmAction)
                    <form action="{{ $confirmAction }}" method="POST">
                        @csrf
                        @method('PATCH')

                        {{-- 本文 --}}
                        <div class="text-gray-700 mb-4">
                            {{ $slot }}
                        </div>

                        {{-- フォーム要素 --}}
                        {{ $form ?? '' }}

                        <div class="flex justify-between gap-2 mt-4">
                            <button
                                type="button"
                                @click="open = false"
                                class="w-1/2 bg-gray-300 text-gray-800 px-3 py-2 z-[9999] rounded hover:bg-gray-400 transition"
                            >
                                戻る
                            </button>

                            <button
                                type="submit"
                                class="w-1/2 {{ $confirmColor }} text-white px-3 py-2 rounded hover:opacity-90 transition"
                            >
                                {{ $confirmText }}
                            </button>
                        </div>
                    </form>
                @endif

            </div>
        </div>
    </template>
</div>
