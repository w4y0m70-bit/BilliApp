@props(['event', 'entry'])
@php
    $max = $event->max_entries;
    
    // 有効な（キャンセルされていない）全エントリーを取得
    $allEntries = $event->userEntries()->where('status', '!=', 'cancelled')->get();

    // 1. 参加確定枠（orderが定員以内のもの）
    $entryCount = $allEntries->where('order', '<=', $max)->where('order', '>', 0)->count();

    // 2. キャンセル待ち枠（orderが定員より大きい、またはstatusがwaitlistのもの）
    $waitlistCount = $allEntries->filter(function($e) use ($max) {
        return $e->order > $max || $e->status === 'waitlist';
    })->count();

    $unit = $event->max_team_size == 2 ? 'チーム' : '名';
@endphp
<div class="p-4 bg-user/5 border border-user/20 rounded-xl space-y-4">
    <label class="block font-bold text-user text-sm flex items-center gap-1">
        <span class="material-icons text-sm">person_add</span>
        パートナーを招待する
    </label>

    {{-- A. パートナーを選択した状態 --}}
    <template x-if="selectedUser">
        <div class="bg-blue-50 border border-blue-200 p-3 rounded-lg flex items-center justify-between">
            <div>
                <p class="text-[10px] text-blue-600 font-bold">招待する相手</p>
                <p class="text-sm font-bold" x-text="selectedUser.full_name + ' (@' + selectedUser.account_name + ')'"></p>
            </div>
            <button type="button" @click="clearSelection()" class="text-gray-400 hover:text-red-500">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
            {{-- フォーム送信用の隠しフィールド --}}
            <input type="hidden" name="partner_id" :value="selectedUser.id">
        </div>
    </template>

    {{-- 2. 検索窓（未選択時のみ表示） --}}
    <template x-if="!selectedUser">
        <div class="relative">
            <input 
                type="text" 
                x-model="searchQuery" 
                @input.debounce.300ms="searchUsers()" 
                placeholder="パートナーの名前・IDで検索"
                class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-user focus:ring-user"
            >
            
            {{-- 検索結果ドロップダウン --}}
            <div x-show="results.length > 0" class="absolute z-50 w-full bg-white border border-gray-200 rounded-md shadow-xl mt-1 overflow-hidden">
                <template x-for="user in results" :key="user.id">
                    <div @click="selectUser(user)" class="p-2 hover:bg-user/10 cursor-pointer border-b last:border-0 flex flex-col">
                        <span class="text-sm font-bold" x-text="user.full_name"></span>
                        <span class="text-[10px] text-gray-500" x-text="'@' + user.account_name"></span>
                    </div>
                </template>
            </div>
        </div>
    </template>

    {{-- 3. 送信ボタン（選択時のみ活性化、または表示） --}}
    <div x-show="selectedUser" class="mt-2">
        <form action="{{ route('user.entries.invite', ['event' => $event->id, 'entry' => $entry->id]) }}" method="POST">
            @csrf
            <input type="hidden" name="partner_id" :value="selectedUser.id">
            <button type="submit" class="w-full bg-user text-white text-sm font-bold py-2 rounded-lg hover:bg-user-dark shadow-sm">
                この人に招待を送る
            </button>
        </form>
    </div>

        {{-- 検索中表示 --}}
        <div x-show="searching" class="absolute right-3 top-2.5">
            <div class="animate-spin h-4 w-4 border-2 border-user border-t-transparent rounded-full"></div>
        </div>
    </div>
</div>