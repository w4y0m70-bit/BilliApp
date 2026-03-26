@props(['groupedLocations'])

<div x-data="{
    filterOpen: false,
    toggleFilter() {
        this.filterOpen = !this.filterOpen;
        document.body.style.overflow = this.filterOpen ? 'hidden' : '';
    },
    toggleCityList(pref) {
        const list = document.getElementById(`list-${pref}`);
        const icon = document.getElementById(`icon-${pref}`);
        if (list.classList.contains('hidden')) {
            list.classList.remove('hidden');
            icon.style.transform = 'rotate(180deg)';
        } else {
            list.classList.add('hidden');
            icon.style.transform = 'rotate(0deg)';
        }
    }
}" @keydown.escape.window="if(filterOpen) toggleFilter()">

    {{-- フィルター起動ボタン --}}
    <button type="button" @click="toggleFilter()"
        class="flex items-center space-x-2 bg-white border border-gray-300 px-3 py-1 rounded-full shadow-sm hover:bg-gray-50 transition">
        <span class="material-symbols-outlined text-gray-600">tune</span>
        {{-- <span class="text-sm font-bold text-gray-700">エリアフィルター</span> --}}
        @if (request()->filled('prefs') || request()->filled('cities'))
            <span
                class="bg-user text-white text-[10px] w-5 h-5 flex items-center justify-center rounded-full font-black">
                {{ count(request('prefs', [])) + count(request('cities', [])) }}
            </span>
        @endif
    </button>

    {{-- オーバーレイ --}}
    <div x-show="filterOpen" x-transition:enter="transition opacity-0 ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition opacity-100 ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" @click="toggleFilter()" class="fixed inset-0 bg-black/50 z-40 shadow-2xl"
        x-cloak></div>

    {{-- ドロワー本体 --}}
    <div x-show="filterOpen" x-transition:enter="transition transform duration-300 ease-in-out"
        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition transform duration-300 ease-in-out" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed top-0 right-0 h-full w-80 sm:w-96 bg-white z-50 shadow-2xl flex flex-col" x-cloak>

        {{-- ヘッダー --}}
        <div class="p-4 border-b flex justify-between items-center bg-gray-50">
            <h3 class="font-bold flex items-center text-gray-700">
                <span class="material-symbols-outlined mr-1">location_on</span>
                エリア選択
            </h3>
            <button type="button" @click="toggleFilter()" class="p-1 hover:bg-gray-200 rounded-full flex items-center">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        {{-- フォームコンテンツ --}}
        <div class="flex-1 overflow-y-auto p-4">
            <form id="filter-form" action="{{ route('user.events.index') }}" method="GET">
                {{-- 既存の検索クエリを保持したい場合はここに追加 --}}
                <div class="space-y-3">
                    @foreach ($groupedLocations as $pref => $cities)
                        <div class="border rounded-lg overflow-hidden border-gray-100 shadow-sm">
                            <div class="flex items-center justify-between bg-gray-50/50 px-3 py-2">
                                <label class="flex items-center space-x-2 cursor-pointer flex-1">
                                    <input type="checkbox" name="prefs[]" value="{{ $pref }}"
                                        @checked(in_array($pref, request('prefs', [])))
                                        class="rounded border-gray-300 text-user focus:ring-user">
                                    <span class="font-bold text-sm text-gray-700">{{ $pref }}</span>
                                </label>
                                <button type="button" @click="toggleCityList('{{ $pref }}')"
                                    class="p-1 hover:bg-gray-200 rounded-full flex items-center">
                                    <span id="icon-{{ $pref }}"
                                        class="material-symbols-outlined text-gray-400 block transition-transform text-xl"
                                        style="{{ collect($cities)->intersect(request('cities', []))->isNotEmpty() ? 'transform: rotate(180deg);' : '' }}">
                                        expand_more
                                    </span>
                                </button>
                            </div>

                            <div id="list-{{ $pref }}"
                                class="{{ collect($cities)->intersect(request('cities', []))->isNotEmpty() ? '' : 'hidden' }} p-3 bg-white grid grid-cols-2 gap-2 border-t border-gray-100">
                                @foreach ($cities as $city)
                                    <label class="flex items-center space-x-2 cursor-pointer group">
                                        <input type="checkbox" name="cities[]" value="{{ $city }}"
                                            @checked(in_array($city, request('cities', [])))
                                            class="rounded border-gray-300 text-user focus:ring-user">
                                        <span
                                            class="text-xs text-gray-500 group-hover:text-user font-medium">{{ $city }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </form>
        </div>

        {{-- フッター（適用ボタン） --}}
        <div class="p-4 border-t bg-gray-50">
            <button form="filter-form" type="submit"
                class="w-full bg-user text-white py-3 rounded-xl font-black text-sm shadow-lg shadow-user/20 active:scale-95 transition-all">
                この条件で絞り込む
            </button>
            <a href="{{ route('user.events.index') }}"
                class="block text-center mt-3 text-xs text-gray-400 font-bold hover:underline">
                条件をリセット
            </a>
        </div>
    </div>
</div>
