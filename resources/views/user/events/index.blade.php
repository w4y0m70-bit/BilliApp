@extends('user.layouts.app')
@section('title', '公開イベント一覧')

@section('content')
    <div class="px-4 py-6 max-w-7xl mx-auto" x-data="{ filterOpen: false }">

        {{-- 1. 招待バナーセクション（より目を引くデザインに） --}}
        @if (isset($invitations) && $invitations->isNotEmpty())
            <div class="mb-10 space-y-4">
                @foreach ($invitations as $invitation)
                    <div
                        class="relative overflow-hidden bg-gradient-to-br from-user to-user-dark text-white p-6 rounded-2xl shadow-xl shadow-user/20 flex flex-col sm:flex-row items-center justify-between gap-6 animate-pulse-subtle border border-white/10">
                        {{-- 背景の装飾アイコン --}}
                        <span
                            class="material-symbols-outlined absolute -right-4 -bottom-4 text-8xl text-white/10 rotate-12 pointer-events-none">celebration</span>

                        <div class="flex items-center gap-4 relative z-10">
                            <div class="bg-white/20 backdrop-blur-md p-3 rounded-xl border border-white/30 shadow-inner">
                                <span class="material-symbols-outlined text-white text-3xl">group_add</span>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span
                                        class="bg-white/20 text-[10px] px-2 py-0.5 rounded-full font-black uppercase tracking-wider">Invitation</span>
                                    <p class="text-xs text-white/90 font-bold">チームの招待が届いています</p>
                                </div>
                                <p class="text-lg font-black leading-tight">
                                    {{ $invitation->representative->full_name }}さんから<br
                                        class="sm:hidden">「{{ $invitation->event->title }}」に誘われています！
                                </p>
                                <div class="flex items-center gap-2 mt-2 text-white/80">
                                    <span class="material-symbols-outlined text-sm">schedule</span>
                                    <p class="text-xs font-bold">
                                        回答期限：{{ $invitation->pending_until->format('m/d H:i') }} まで
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="w-full sm:w-auto relative z-10">
                            <a href="{{ route('user.events.show', $invitation->event_id) }}"
                                class="block w-full sm:w-auto bg-white text-user px-8 py-3 rounded-xl text-sm font-black hover:bg-gray-50 transition-all text-center shadow-lg active:scale-95">
                                詳細を確認して回答する
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- 2. ヘッダーセクション --}}
        <div class="py-2">
            <div class="flex justify-between">
                <h2 class="text-3xl font-black text-gray-800 flex items-center tracking-tight">
                    公開中のイベント
                    <x-help help-key="user.events.index" class="ml-2" />
                </h2>
                {{-- 分離したフィルターコンポーネントを呼び出す --}}
                <x-user.event-filter :groupedLocations="$groupedLocations" />
            </div>

        </div>
        {{-- クイックフィルタ & ソートセクション --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">

            {{-- 左側：状態フィルタ（タブ） --}}
            <div class="flex p-1 bg-gray-100 rounded-xl overflow-x-auto no-scrollbar w-full md:w-auto">
                @php
                    $currentStatus = request('status_filter', '');
                @endphp
                <a href="{{ request()->fullUrlWithQuery(['status_filter' => null]) }}"
                    class="flex-1 md:flex-none px-6 py-2 rounded-lg text-xs font-black text-center whitespace-nowrap transition-all {{ $currentStatus === '' ? 'bg-white shadow-sm text-user' : 'text-gray-400 hover:text-gray-600' }}">
                    すべて
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status_filter' => 'entry_all']) }}"
                    class="flex-1 md:flex-none px-6 py-2 rounded-lg text-xs font-black text-center whitespace-nowrap transition-all {{ $currentStatus === 'entry_all' ? 'bg-white shadow-sm text-user' : 'text-gray-400 hover:text-gray-600' }}">
                    エントリー中
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status_filter' => 'not_entry']) }}"
                    class="flex-1 md:flex-none px-6 py-2 rounded-lg text-xs font-black text-center whitespace-nowrap transition-all {{ $currentStatus === 'not_entry' ? 'bg-white shadow-sm text-user' : 'text-gray-400 hover:text-gray-600' }}">
                    未エントリー
                </a>
            </div>

            {{-- 右側：ソート選択 --}}
            <div class="flex items-center gap-2 self-end w-full md:w-auto justify-end">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest hidden sm:inline">並び替え</span>
                <select onchange="location.href=this.value"
                    class="w-full md:w-auto text-xs font-bold border-gray-200 rounded-xl focus:ring-user focus:border-user py-2 pl-3 pr-10 bg-white shadow-sm">
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'date_asc']) }}" @selected(request('sort') === 'date_asc' || !request('sort'))>
                        開催日が近い順</option>
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'deadline_asc']) }}"
                        @selected(request('sort') === 'deadline_asc')>エントリー締切が近い順</option>
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" @selected(request('sort') === 'newest')>
                        新着順</option>
                </select>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
            {{-- 3. イベントカードグリッド --}}
            @if ($events->isEmpty())
                <div class="bg-white border-2 border-dashed border-gray-200 rounded-3xl p-5 text-center">
                    <span class="material-symbols-outlined text-gray-300 text-6xl mb-4">event_busy</span>
                    <p class="text-gray-500 font-bold">現在、公開中のイベントはありません。</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                    @foreach ($events as $event)
                        {{-- コンポーネントを呼び出す --}}
                        <x-user.event-card :event="$event" />
                    @endforeach
                </div>
            @endif
        </div>
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
                @foreach ($groupedLocations as $pref => $cities)
                    @if (collect($cities)->intersect(request('cities', []))->isNotEmpty())
                        toggleCityList('{{ $pref }}');
                    @endif
                @endforeach
            });
        </script>
    @endsection
