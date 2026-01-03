@if($help)
<span x-data="{ open: false }">
    <!-- ? アイコン -->
    <span
        class="material-symbols-outlined
            text-gray-400
            cursor-pointer
            ml-1
            leading-none"
        style="
            font-size: 14px;
            font-variation-settings:
                'FILL' 0,
                'wght' 400,
                'GRAD' 0,
                'opsz' 10;
        "
        @click="open = true"
    >
        help
    </span>


    <!-- モーダル全体 -->
    <div
        x-show="open"
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center px-4"
       @click.prevent="open = false"
    >
        <!-- 背景 -->
        <div class="absolute inset-0 bg-black/40"></div>

        <!-- モーダル本体 -->
        <div
            class="relative w-full max-w-md bg-white rounded-lg shadow-lg p-5 text-sm text-gray-700"
            @click.stop
        >
            <!-- タイトル + 閉じる -->
            <div class="flex justify-between items-start mb-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-400">
                        help
                    </span>

                    <!-- タイトル -->
                    <h2 class="font-semibold text-base text-gray-900">
                        {{ $help['title'] ?? '' }}
                    </h2>
                </div>

                <!-- 閉じる -->
                <button
                    type="button"
                    class="material-symbols-outlined text-gray-400"
                   @click.prevent="open = false"
                >
                    close
                </button>
            </div>

            @if($help)
                @foreach(($help['body'] ?? []) as $line)
                    <p class="text-sm text-gray-700 mt-2">
                        {{ $line }}
                    </p>
                @endforeach
            @else
                @env('local')
                    <p class="text-red-600 text-xs">
                        help 未定義
                    </p>
                @endenv
            @endif

        </div>
    </div>
</span>
@endif
