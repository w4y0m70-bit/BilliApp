@extends('user.layouts.app')

@section('title', '公開イベント一覧')

@section('content')
<div class="px-4">
    <h2 class="text-2xl font-bold">公開中のイベント
    <span help-key="user.events.index" class="inline-block mb-4">
        <x-help help-key="user.events.index" />
    </span>
    </h2>

    {{-- エリアフィルター 完成しているが今は非表示--}}
    <!-- <div class="mb-2 flex justify-end">
        <button type="button" onclick="toggleFilter()" class="flex items-center space-x-2 bg-white border border-gray-300 px-4 py-2 rounded-full shadow-sm hover:bg-gray-50 transition">
            <span class="material-symbols-outlined text-gray-600">tune</span>
            <span class="text-sm font-bold text-gray-700">エリアを絞り込む</span>
            @if(request()->filled('prefs') || request()->filled('cities'))
                <span class="bg-user text-white text-[10px] w-5 h-5 flex items-center justify-center rounded-full">
                    {{ count(request('prefs', [])) + count(request('cities', [])) }}
                </span>
            @endif
        </button>
    </div> -->

    <div id="filter-overlay" onclick="toggleFilter()" class="fixed inset-0 bg-black/50 z-40 hidden transition-opacity"></div>

    <div id="filter-drawer" class="fixed top-0 right-0 h-full w-80 sm:w-96 bg-white z-50 shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
        
        <div class="p-4 border-b flex justify-between items-center bg-gray-50">
            <h3 class="font-bold flex items-center text-gray-700">
                <span class="material-symbols-outlined mr-1">location_on</span>
                エリア選択
            </h3>
            <button type="button" onclick="toggleFilter()" class="p-1 hover:bg-gray-200 rounded-full">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4">
            <form id="filter-form" action="{{ route('user.events.index') }}" method="GET">
                <div class="space-y-3">
                    @foreach($groupedLocations as $pref => $cities)
                        <div class="border rounded-lg overflow-hidden border-gray-200">
                            <div class="flex items-center justify-between bg-gray-50 px-3 py-2">
                                <label class="flex items-center space-x-2 cursor-pointer flex-1">
                                    <input type="checkbox" name="prefs[]" value="{{ $pref }}" 
                                        @checked(in_array($pref, request('prefs', [])))
                                        class="rounded border-gray-300 text-user focus:ring-user">
                                    <span class="font-bold text-sm text-gray-700">{{ $pref }}</span>
                                </label>
                                <button type="button" onclick="toggleCityList('{{ $pref }}')" class="p-1 hover:bg-gray-200 rounded-full">
                                    <span id="icon-{{ $pref }}" class="material-symbols-outlined text-gray-400 block transition-transform">expand_more</span>
                                </button>
                            </div>

                            <div id="list-{{ $pref }}" class="hidden p-3 bg-white grid grid-cols-2 gap-2 border-t border-gray-100">
                                @foreach($cities as $city)
                                    <label class="flex items-center space-x-2 cursor-pointer group">
                                        <input type="checkbox" name="cities[]" value="{{ $city }}" 
                                            @checked(in_array($city, request('cities', [])))
                                            class="rounded border-gray-300 text-user focus:ring-user">
                                        <span class="text-xs text-gray-500 group-hover:text-user">{{ $city }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </form>
        </div>

        <div class="p-4 border-t bg-white flex flex-col space-y-2">
            <button type="submit" form="filter-form" class="w-full bg-user text-white py-3 rounded-full font-bold shadow-lg text-sm">
                この条件で絞り込む
            </button>
            <a href="{{ route('user.events.index') }}" class="text-center text-xs text-gray-400 hover:text-gray-600 underline py-1">
                条件をリセット
            </a>
        </div>
    </div>

    @if($events->isEmpty())
        <p>公開中のイベントはありません。</p>
    @else
    <div 
        class="grid gap-4"
        style="
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        "
    >
        @foreach ($events as $event)
            @php
                $currentUser = Auth::user();
                $userEntry = $event->userEntries()
                    ->where('user_id', $currentUser->id)
                    ->where('status', '!=', 'cancelled')
                    ->latest('created_at')
                    ->first();

                $status = $userEntry->status ?? null;
                $isFull = $event->entry_count >= $event->max_participants;
            @endphp

            {{-- カード部分 --}}
            <div class="block bg-white shadow rounded-xl p-4 border hover:shadow-lg transition">
                <div class="flex justify-between items-center mb-1">
                <p class="text-sm font-bold text-gray-600">
                    ［{{ $event->organizer->name ?? '主催者不明' }}］
                </p>

                @if($event->requiredGroups->isNotEmpty())
                    <div class="flex flex-wrap gap-1 justify-end">
                        @foreach($event->requiredGroups as $group)
                            <span class="inline-flex items-center text-[10px] px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-bold border border-blue-200">
                                {{-- 盾や鍵のアイコンを入れると「限定感」が出ます --}}
                                <!-- <span class="material-symbols-outlined text-[12px] mr-0.5">verified_user</span> -->
                                {{ $group->name }}限定
                            </span>
                        @endforeach
                    </div>
                @endif
                </div>

                {{-- タイトルをリンクに --}}
                <h3 class="text-2xl font-black mb-1 text-user">
                    <a href="{{ route('user.events.show', $event->id) }}" class="hover:underline">
                        {{ $event->title }}
                    </a>
                    <x-help help-key="user.events.show" />
                </h3>

                {{-- 募集クラス (追加部分) --}}
                <div class="mt-2 flex items-start gap-1">
                    <strong class="text-sm text-gray-700">参加資格：</strong>
                    <div class="flex flex-wrap gap-1">
                        @php
                            $eventClasses = $event->eventClasses;
                        @endphp

                        @forelse($eventClasses as $eventClass)
                            @php
                                $classEnum = App\Enums\PlayerClass::tryFrom($eventClass->class_name);
                            @endphp
                            <span class="inline-flex items-center text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-700 border border-gray-300 font-medium">
                                {{ $classEnum ? $classEnum->shortLabel() : $eventClass->class_name }}
                            </span>
                        @empty
                            <span class="text-xs text-gray-400">制限なし</span>
                        @endforelse
                    </div>
                </div>
                <p class="text-sm text-gray-700">
                    <strong>開催日時：</strong><span class="text-lg font-bold">
                    {{ format_event_date($event->event_date) }}
                    {{ $event->event_date->format('H:i') }}
                </span></p>

                <p class="text-sm text-gray-700 mt-1">
                    <strong>エントリー締切：</strong>
                    {{ format_event_date($event->entry_deadline) }}
                    {{ $event->entry_deadline->format('H:i') }}
                </p>

                <div class="flex items-center">
                    <p class="text-sm text-gray-700 mt-1">
                        <strong>キャンセル待ち期限：</strong>
                        @if ($status === 'waitlist' && $userEntry->waitlist_until)
                            {{ format_event_date($userEntry->waitlist_until) }}
                            {{ $userEntry->waitlist_until->format('H:i') }}
                        @else
                            —
                        @endif
                    </p>
                    <x-help help-key="user.events.waitlist_until" />
                </div>

                {{-- ★ 修正箇所：参加人数の数字をリンクにする --}}
                <div class="flex items-center mb-1">
                <p class="text-sm text-gray-700 mt-1">
                    <strong>参加人数：</strong>
                    <a href="{{ route('user.events.participants', $event->id) }}" class="text-blue-600 hover:underline font-bold">
                        {{ $event->entry_count }}
                        ／{{ $event->max_participants }}人
                        （
                        @if($event->allow_waitlist)
                        WL：{{ $event->waitlist_count }}
                        @else
                        ✕
                        @endif
                        ）
                    </a>
                </p>
                <x-help help-key="user.events.participants" />
                </div>

                {{-- 状態グループ --}}
                <div class="mt-3">
                    @if ($status === 'entry')
                            <span class="inline-block bg-user text-white text-sm px-3 py-1 rounded transition">
                                エントリー中
                            </span>
                        @elseif ($status === 'waitlist')
                            <span class="inline-block bg-orange-500 text-white text-sm px-3 py-1 rounded transition">
                                キャンセル待ち（{{ $userEntry->waitlist_position ?? '' }}番目）
                            </span>
                        @else
                            <span class="inline-block bg-gray-400 text-white text-sm px-3 py-1 rounded transition">
                                @if($isFull && !$event->allow_waitlist)
                                満員
                                @else
                                未エントリー
                                @endif
                            </span>
                        @endif
                </div>
            </div>
        @endforeach
    </div>
    @endif
</div>

{{-- 過去にエントリーしたイベント --}}
@if(isset($pastEntries) && $pastEntries->count() > 0)
<div class="bg-gray-50 shadow rounded-lg p-6 mt-8">
    <h2 class="text-xl font-bold mb-4">過去にエントリーしたイベント</h2>
    @foreach ($pastEntries as $entry)
        <div class="p-3 border-b last:border-0">
            <strong>{{ $entry->event->title }}</strong>
            <span class="text-sm text-gray-600">{{ $entry->event->event_date->format('Y/m/d H:i') }}</span>
        </div>
    @endforeach
</div>
@endif

<script>
/**
 * 市区町村リストの表示・非表示を切り替える
 */
function toggleFilter() {
    const drawer = document.getElementById('filter-drawer');
    const overlay = document.getElementById('filter-overlay');
    
    if (drawer.classList.contains('translate-x-full')) {
        // 開く
        drawer.classList.remove('translate-x-full');
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // 背景スクロール禁止
    } else {
        // 閉じる
        drawer.classList.add('translate-x-full');
        overlay.classList.add('hidden');
        document.body.style.overflow = ''; // スクロール復帰
    }
}
function toggleCityList(pref) {
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

// ページ読み込み時、既に市区が選択されている都道府県は開いておく
window.addEventListener('DOMContentLoaded', () => {
    @foreach($groupedLocations as $pref => $cities)
        @if(collect($cities)->intersect(request('cities', []))->isNotEmpty())
            toggleCityList('{{ $pref }}');
        @endif
    @endforeach
});
</script>

@endsection
