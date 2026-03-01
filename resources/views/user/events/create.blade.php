@extends('user.layouts.app')

@section('title', 'エントリー情報の入力')

@section('content')
<div class="bg-white shadow rounded-lg p-6 max-w-lg mx-auto" 
     x-data="pairSearch()">
    <p class="text-sm text-gray-600">【 {{ $event->organizer->name ?? '主催者不明' }} 】</p>
    <h2 class="text-xl font-bold mb-4">{{ $event->title }}</h2>

    <form action="{{ route('user.entries.entry', $event->id) }}" method="POST" class="space-y-6">
        @csrf

        {{-- 1. クラス選択（ここは既存のまま） --}}
        {{-- ... (中略) ... --}}
        @php
            $userClass = auth()->user()->class;
            if (!$userClass instanceof \App\Enums\PlayerClass) {
                $userClass = \App\Enums\PlayerClass::tryFrom($userClass);
            }
            $defaultClass = null;
            if ($userClass && $event->eventClasses->isNotEmpty()) {
                $userRank = $userClass->rank();
                $bestMatch = $event->eventClasses->map(function($item) use ($userRank) {
                    $classEnum = \App\Enums\PlayerClass::tryFrom($item->class_name);
                    $rank = $classEnum ? $classEnum->rank() : 0;
                    return [
                        'class_name' => $item->class_name,
                        'distance' => abs($rank - $userRank),
                        'rank' => $rank
                    ];
                })
                ->sortBy([['distance', 'asc'], ['rank', 'desc']])
                ->first();
                $defaultClass = $bestMatch['class_name'] ?? null;
            }
        @endphp

        <div>
            <div class="flex items-center mb-2">
                <label class="block font-bold mb-2">クラスの申告</label>
                <x-help help-key="user.entries.class" />
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach($event->eventClasses as $class)
                    @php $currentClassEnum = \App\Enums\PlayerClass::tryFrom($class->class_name); @endphp
                    <label class="flex items-center gap-2 p-3 border rounded cursor-pointer hover:bg-gray-50 has-[:checked]:border-user has-[:checked]:bg-user/5">
                        <input type="radio" name="class" value="{{ $class->class_name }}" required
                            {{ old('class', $defaultClass) == $class->class_name ? 'checked' : '' }}>
                        <span class="font-medium">
                            {{ $currentClassEnum ? $currentClassEnum->shortLabel() : $class->class_name }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- 2. 伝達事項 --}}
        @if($event->instruction_label)
            <div>
                <label class="block font-bold mb-1">{{ $event->instruction_label }}</label>
                <textarea name="user_answer" rows="3" 
                    class="w-full border rounded p-2 focus:ring-user focus:border-user"
                    placeholder="こちらに入力してください">{{ old('user_answer') }}</textarea>
            </div>
        @endif

        {{-- 3. キャンセル待ち設定 --}}
        @if($event->entry_count >= $event->max_participants)
            <div class="bg-yellow-50 p-3 rounded text-sm text-yellow-800 border border-yellow-200">
                <span class="material-icons text-sm align-middle">hourglass_empty</span>
                現在満員のため、<strong>キャンセル待ち</strong>としての登録となります。
            </div>
        @endif

        {{-- チームエントリーの場合の検索窓（募集人数が1より大きい場合） --}}
        @if($event->max_team_size > 1)
            <div class="p-4 bg-user/5 border border-user/20 rounded-xl space-y-4">
                <label class="block font-bold text-user">
                    <span class="material-icons text-sm align-middle">group_add</span>
                    チームメンバーを招待
                </label>
                
                {{-- 選択済みメンバーの表示 --}}
                <template x-if="selectedUser">
                    <div class="flex items-center justify-between bg-white p-3 rounded-lg border border-user shadow-sm">
                        <div>
                            <span class="text-xs text-gray-500 block">招待する相手</span>
                            <span class="font-bold text-gray-800" x-text="selectedUser.name"></span>
                            <span class="text-xs text-gray-500" x-text="'@' + selectedUser.account_name"></span>
                        </div>
                        <button type="button" @click="clearSelection()" class="text-red-500 hover:text-red-700">
                            <span class="material-icons">cancel</span>
                        </button>
                        {{-- フォーム送信用の隠しフィールド --}}
                        <input type="hidden" name="partner_id" :value="selectedUser.id">
                    </div>
                </template>

                {{-- 検索入力 --}}
                <div x-show="!selectedUser" class="relative">
                    <input type="text" 
                           x-model="searchQuery" 
                           @input.debounce.300ms="searchUsers()"
                           class="w-full border rounded-lg p-3 focus:ring-user focus:border-user" 
                           placeholder="アカウント名、氏名で検索...">
                    
                    {{-- 検索結果ドロップダウン --}}
                    <div x-show="results.length > 0" 
                         class="absolute z-10 w-full bg-white border rounded-lg mt-1 shadow-xl max-h-60 overflow-y-auto"
                         x-cloak>
                        <template x-for="user in results" :key="user.id">
                            <button type="button" 
                                    @click="selectUser(user)"
                                    class="w-full text-left p-3 hover:bg-user/5 border-b last:border-0 transition flex flex-col">
                                <span class="font-bold text-gray-800" x-text="user.full_name"></span>
                                <span class="text-xs text-gray-500" x-text="'@' + user.account_name"></span>
                            </button>
                        </template>
                    </div>

                    {{-- 検索中・ヒットなしの表示 --}}
                    <div x-show="searching" class="absolute right-3 top-3">
                        <div class="animate-spin h-5 w-5 border-2 border-user border-t-transparent rounded-full"></div>
                    </div>
                </div>
                <p class="text-[10px] text-gray-500">※招待した相手が承諾するまでエントリーは完了しません。</p>
            </div>
        @endif

        <div class="flex flex-col gap-3 pt-4 border-t">
            <button type="submit" 
                    :disabled="{{ $event->max_team_size > 1 ? '!selectedUser' : 'false' }}"
                    class="w-full bg-user text-white py-3 rounded-lg font-bold hover:bg-user-dark transition disabled:bg-gray-300 disabled:cursor-not-allowed">
                {{ $event->max_team_size > 1 ? '招待を送ってエントリー' : 'この内容でエントリーする' }}
            </button>
            <a href="{{ route('user.events.show', $event->id) }}" class="text-center text-user text-sm underline">
                戻る
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function pairSearch() {
    return {
        searchQuery: '',
        results: [],
        selectedUser: null,
        searching: false,

        async searchUsers() {
            if (this.searchQuery.length < 2) {
                this.results = [];
                return;
            }
            this.searching = true;
            try {
                const url = `{{ route('user.users.search') }}?q=${encodeURIComponent(this.searchQuery)}`;
                const response = await fetch(url);
                const data = await response.json();
                this.results = data;
            } catch (error) {
                console.error('Search error:', error);
            } finally {
                this.searching = false;
            }
        },

        selectUser(user) {
            this.selectedUser = {
                id: user.id,
                name: user.full_name,
                account_name: user.account_name
            };
            this.results = [];
            this.searchQuery = '';
        },

        clearSelection() {
            this.selectedUser = null;
        }
    }
}
</script>
@endpush
@endsection