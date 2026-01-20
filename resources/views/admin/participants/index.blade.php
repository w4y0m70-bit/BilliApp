@extends('admin.layouts.app')

@section('title', $event->title . ' 参加者一覧')

@section('content')
<div class="px-4">
<div 
    x-data="participantManager({{ $event->id }}, {{ $event->max_participants }})"
    x-init="loadParticipants()"
    class="space-y-3"
>
    <div>
        <!-- 1行目：戻る -->
        <a href="{{ route('admin.events.index') }}" class="text-admin hover:text-gray-800 flex items-center">
            <span class="material-icons">arrow_back</span>
            <span>戻る</span>
        </a>

        <!-- 2行目：タイトル -->
            <h2 class="text-2xl font-bold">{{ $event->title }} の参加者一覧</h2>
        
        <!-- 3行目：参加人数 -->
         <div class="flex justify-between items-center">
            <p class="text-gray-700">
                エントリー：
                <span x-text="participants.filter(e => e.status === 'entry').length"></span>
                /
                {{ $event->max_participants }}
                （キャンセル待ち：
                <span x-text="participants.filter(e => e.status === 'waitlist').length"></span>
                ）
            </p>
            <!-- ゲスト追加ボタン -->
            <div class="flex items-center gap-1">
                <x-help help-key="admin.participants.add_guest" />

                <button
                    type="button"
                    @click="openModal = true"
                    class="bg-admin text-white px-3 py-1 rounded hover:bg-admin-dark
                        flex items-center justify-center"
                >
                    <span class="material-icons text-lg">add</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 参加者表 -->
    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-300">
            <thead class="bg-gray-300">
                <tr>
                    <th class="px-4 border-b w-2/12">No.</th>
                    <th class="px-4 border-b w-4/12">名　前</th>
                    <th class="px-4 border-b w-4/12">クラス</th>
                    <th class="px-4 border-b w-2/12">削除</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="entry in sortedParticipants" :key="entry.id">
                    <tr :class="entry.status === 'waitlist' ? 'bg-yellow-100' : 'bg-white'">
                        <td class="px-1 py-2 border-b text-center font-medium">
                            <span x-text="entry.status === 'entry' ? entry.order : ('WL-' + entry.order)"></span>
                        </td>
                        <td class="px-1 py-2 border-b font-bold">
                            <span :class="entry.gender === '女性' ? 'text-pink-500' : ''" x-text="entry.name"></span>
                        </td>
                        <td class="px-1 py-2 border-b text-center text-gray-600">
                            <span x-text="entry.class ? (classShortLabels[entry.class] || entry.class) : '??'"></span>
                        </td>
                        <td class="px-1 py-2 border-b text-center">
                            <button 
                                @click="cancelEntry(entry.id)" 
                                class="text-red-500 hover:text-red-700 text-xs"
                                title="キャンセル"
                            >✕</button>
                        </td>
                    </tr>
                </template>

                <template x-if="participants.length === 0">
                    <tr>
                        <td colspan="4" class="px-4 py-4 text-center text-gray-500">参加者はいません</td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- ゲスト追加モーダル -->
    <div x-show="openModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50">
        <div @click.away="openModal = false" class="bg-white rounded-lg p-6 w-full max-w-md shadow-lg">
            <h3 class="text-xl font-bold mb-4">ゲストエントリー登録</h3>

            <form @submit.prevent="addGuest">
                <div class="mb-4">
                    <label class="block mb-1 font-medium">名前</label>
                    <input type="text" x-model="guest.name" class="border rounded w-full px-3 py-2" required>
                </div>

                <div class="mb-4">
                    <label class="block mb-1 font-medium">性別</label>
                    <select x-model="guest.gender" class="border rounded w-full px-3 py-2">
                        <option value="">選択してください</option>
                        <option value="男性">男性</option>
                        <option value="女性">女性</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block mb-1 font-medium">クラス</label>
                    <select x-model="guest.class" class="border rounded w-full px-3 py-2">
                        <option value="">選択してください</option>
                        @foreach(\App\Enums\PlayerClass::cases() as $classOption)
                            <option value="{{ $classOption->value }}">
                                {{ $classOption->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="openModal=false" class="px-4 py-2 rounded border text-gray-600 hover:bg-gray-100">閉じる</button>
                    <button type="submit" class="bg-admin text-white px-4 py-2 rounded hover:bg-admin-dark">登録</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const classShortLabels = {{ Js::from(
            collect(App\Enums\PlayerClass::cases())
                ->mapWithKeys(fn($case) => [$case->value => $case->shortLabel()])
        ) }};
        function participantManager(eventId, maxParticipants) {
        return {
            openModal: false,
            participants: [],
            guest: { name: '', gender: '', class: '' },

            async loadParticipants() {
                const res = await fetch(`/admin/events/${eventId}/participants/json`);
                const list = await res.json();

                const sorted = list.sort((a, b) => a.status === b.status ? 0 : (a.status === 'entry' ? -1 : 1));
                this.participants = sorted;
            },

            get sortedParticipants() { return this.participants; },

            async addGuest() {
                if (!this.guest.name) return;

                const currentEntryCount = this.participants.filter(e => e.status === 'entry').length;
                const status = currentEntryCount < maxParticipants ? 'entry' : 'waitlist';

                const payload = { 
                    name: this.guest.name, 
                    gender: this.guest.gender,
                    class: this.guest.class,
                    status
                };

                const res = await fetch(`/admin/events/${eventId}/participants`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });

                if (res.ok) {
                    this.guest = { name: '', gender: '', class: '' };
                    this.openModal = false;
                    await this.loadParticipants();
                }
            },

            async cancelEntry(entryId) {
                if (!confirm('この参加者をキャンセルしますか？\n【注意】　この操作は取り消せません')) return;

                const res = await fetch(`/admin/events/${eventId}/participants/${entryId}/cancel`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                if (res.ok) {
                    const data = await res.json();
                    alert(data.message);
                    await this.loadParticipants();
                } else {
                    alert('キャンセルに失敗しました');
                }
            }
        }
    }
</script>
@endsection
