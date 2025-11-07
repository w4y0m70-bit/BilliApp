@extends('admin.layouts.app')

@section('title', $event->title . ' 参加者一覧')

@section('content')
<div 
    x-data="participantManager({{ $event->id }}, {{ $event->max_participants }})"
    x-init="loadParticipants()"
    class="space-y-6"
>
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">{{ $event->title }} の参加者一覧</h2>

        <div class="flex items-center gap-4">
            <p class="text-gray-700">参加者：<span x-text="participants.filter(e => e.status==='entry').length"></span>名</p>

            <a href="{{ route('admin.events.index') }}" class="text-gray-500 hover:underline">
                ← イベント一覧へ戻る
            </a>
            <!-- ゲスト追加ボタン -->
            <button 
                @click="openModal = true"
                class="bg-admin text-white px-4 py-2 rounded hover:bg-admin-dark"
            >
                ＋ ゲストを追加
            </button>

        </div>
    </div>

    <!-- 参加者カード -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        <template x-for="(entry, index) in sortedParticipants" :key="entry.id">
           <div 
    class="relative shadow rounded-lg p-4 flex items-center gap-2"
    :class="entry.status === 'waitlist' ? 'bg-yellow-100' : 'bg-white'"
>
    <!-- 左に番号 or WL -->
    <div 
        class="font-medium w-8 text-center rounded bg-gray-500 text-white py-1"
    >
        <span 
            x-text="entry.status === 'entry' ? (entry.order + 1) : 'WL'"
        ></span>
    </div>

    <!-- 名前 -->
    <div class="flex-1 font-bold" x-text="entry.name ?? (entry.user?.name ?? 'ゲスト')"></div>

    <!-- キャンセルボタン -->
    <button 
        @click="cancelEntry(entry.id)"
        class="text-red-500 hover:text-red-700 text-sm"
        title="キャンセル"
    >
        ✕
    </button>
</div>

        </template>

        <template x-if="participants.length === 0">
            <p class="col-span-3 text-center text-gray-500">参加者はいません</p>
        </template>
    </div>

    <!-- ゲスト追加モーダル -->
    <div 
        x-show="openModal"
        x-cloak
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50"
    >
        <div 
            @click.away="openModal = false"
            class="bg-white rounded-lg p-6 w-full max-w-md shadow-lg"
        >
            <h3 class="text-xl font-bold mb-4">ゲストエントリー登録</h3>

            <form @submit.prevent="addGuest">
                <div class="mb-4">
                    <label for="guestName" class="block mb-1 font-medium">名前</label>
                    <input 
                        type="text" 
                        x-model="guest.name"
                        id="guestName" 
                        class="border rounded w-full px-3 py-2 focus:outline-none focus:ring focus:ring-admin"
                        required
                    >
                </div>

                <div class="flex justify-end gap-3">
                    <button 
                        type="button" 
                        @click="openModal = false"
                        class="px-4 py-2 rounded border text-gray-600 hover:bg-gray-100"
                    >
                        閉じる
                    </button>
                    <button 
                        type="submit" 
                        class="bg-admin text-white px-4 py-2 rounded hover:bg-admin-dark"
                    >
                        登録
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Alpine.js + Ajax管理スクリプト -->
<script>
function participantManager(eventId, maxParticipants) {
    return {
        openModal: false,
        participants: [],
        guest: { name: '' },

        async loadParticipants() {
            const res = await fetch(`/admin/events/${eventId}/participants/json`);
            const list = await res.json();

            // 通常エントリーを先頭、キャンセル待ちは後ろに並べ替え
            const sorted = list.sort((a, b) => {
                if(a.status === b.status) return 0;
                return a.status === 'entry' ? -1 : 1;
            });

            // 通常エントリーに順番(order)を付与
            let counter = 0;
            sorted.forEach(e => {
                e.order = e.status === 'entry' ? counter++ : null;
            });

            this.participants = sorted;
        },

        get sortedParticipants() {
            return this.participants;
        },

        async addGuest() {
            if (!this.guest.name) return;

            // 現在の通常エントリー数をカウント
            const currentEntryCount = this.participants.filter(e => e.status === 'entry').length;
            const status = currentEntryCount < maxParticipants ? 'entry' : 'waitlist';

            const payload = { name: this.guest.name, status };

            const res = await fetch(`/admin/events/${eventId}/participants`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                this.guest = { name: '' };
                this.openModal = false;
                await this.loadParticipants();
            }
        },

        async cancelEntry(entryId) {
            if (!confirm('この参加者をキャンセルしますか？')) return;
            await fetch(`/admin/events/${eventId}/participants/${entryId}/cancel`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            await this.loadParticipants();
        }
    }
}
</script>
@endsection
