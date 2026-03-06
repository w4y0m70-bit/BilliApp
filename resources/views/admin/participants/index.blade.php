@extends('admin.layouts.app')

@section('title', $event->title . ' 参加者一覧')

@section('content')
<div class="px-4">
<div 
    x-data="participantManager({{ $event->id }}, {{ $event->max_participants }})"
    class="space-y-3"
>
    <div>
        <a href="{{ route('admin.events.index') }}" class="text-admin hover:text-gray-800 flex items-center">
            <span class="material-icons">arrow_back</span>
            <span>戻る</span>
        </a>

        <div class="flex justify-between items-center mb-1">
            <h2 class="text-2xl font-bold">{{ $event->title }} の参加者一覧</h2>
            <div class="flex flex-wrap gap-1">
                @foreach($event->requiredGroups as $group)
                    <span class="inline-flex items-center text-[10px] px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-bold border border-blue-200 shadow-sm">
                        {{ $group->name }}限定
                    </span>
                @endforeach
            </div>
        </div>
        
        <div class="flex justify-between items-center">
            <p class="text-gray-700">
                エントリー：
                {{-- $participants は UserEntry のコレクションなので、count() するだけでチーム数になります --}}
                <span class="font-bold text-lg text-user">
                    {{ $participants->where('status', 'entry')->count() }}
                </span>
                /
                {{-- 人数ではなく「募集枠数（チーム数）」を表示 --}}
                {{ $event->max_entries }} {{ $event->max_team_size == 2 ? 'チーム' : '名' }}
                
                <span class="text-sm ml-2 text-gray-500">
                    （キャンセル待ち：
                    <span class="font-bold">
                        {{ $participants->where('status', 'waitlist')->count() }}
                    </span>
                    ）
                </span>
            </p>
            <div class="flex items-center gap-1">
                <x-help help-key="admin.participants.add_guest" />
                <button type="button" @click="openModal = true" class="bg-admin text-white px-3 py-1 rounded hover:bg-admin-dark flex items-center justify-center">
                    <span class="material-icons text-lg">add</span>
                </button>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <div id="participant-table-container">
            <x-event.participant-list 
                :participants="$participants" 
                mode="admin"
            />
        </div>
    </div>

    <div x-show="openModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50">
        <div @click.away="openModal = false" class="bg-white rounded-lg p-6 w-full max-w-md shadow-lg">
            <h3 class="text-xl font-bold mb-4">ゲストエントリー登録</h3>

            <form @submit.prevent="addGuest">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block mb-1 font-medium text-sm">姓<span class="text-red-500 ml-1">*</span></label>
                        <input type="text" x-model="guest.last_name" class="border rounded w-full px-3 py-2" placeholder="例: 山田" required>
                    </div>
                    <div>
                        <label class="block mb-1 font-medium text-sm">名<span class="text-red-500 ml-1">*</span></label>
                        <input type="text" x-model="guest.first_name" class="border rounded w-full px-3 py-2" placeholder="例: 太郎" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block mb-1 font-medium text-sm">セイ（カナ）</label>
                        <input type="text" x-model="guest.last_name_kana" class="border rounded w-full px-3 py-2" placeholder="ヤマダ">
                    </div>
                    <div>
                        <label class="block mb-1 font-medium text-sm">メイ（カナ）</label>
                        <input type="text" x-model="guest.first_name_kana" class="border rounded w-full px-3 py-2" placeholder="タロウ">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">性別<span class="text-red-500 ml-1">*</span></label>
                    <div class="flex gap-4">
                        <template x-for="(label, value) in {'男性':'男性', '女性':'女性', '未回答':'回答しない'}" :key="value">
                            <label class="inline-flex items-center">
                                <input type="radio" 
                                    name="guest_gender" 
                                    :value="value" 
                                    x-model="guest.gender" 
                                    class="text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-600" x-text="label"></span>
                            </label>
                        </template>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block mb-1 font-medium text-sm">クラス</label>
                    <select x-model="guest.class" class="border rounded w-full px-3 py-2">
                        <option value="">選択してください</option>
                        @foreach(\App\Enums\PlayerClass::cases() as $classOption)
                            <option value="{{ $classOption->value }}">{{ $classOption->label() }}</option>
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
@endsection
@push('scripts')
<script>
    // 1. グローバル関数の定義（window直下に配置）
    window.globalCancelEntry = function(eventId, entryId) {
    if (!confirm('この参加者をキャンセルしますか？')) return;
    
    // 方法1: メタタグから取得（名前を 'csrf-token' で再確認）
    let token = document.querySelector('meta[name="csrf-token"]')?.content;
    
    // 方法2: 見つからない場合の予備（Bladeの関数を直接使う）
    if (!token) {
        token = '{{ csrf_token() }}'; 
    }

    fetch(`/admin/events/${eventId}/participants/${entryId}/cancel`, {
        method: 'PATCH',
        headers: { 
            'X-CSRF-TOKEN': token, // ここでトークンをセット
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(res => {
        if (res.ok) {
            window.location.reload();
        } else {
            alert('キャンセルに失敗しました（Status: ' + res.status + '）');
        }
    })
    .catch(err => console.error('通信エラー:', err));
};

    // 2. Alpine.js 用のマネージャー関数
    function participantManager(eventId, maxParticipants) {
        // クラスラベル用の定数（PHPから注入）
        const classShortLabels = {{ Js::from(
            collect(App\Enums\PlayerClass::cases())
                ->mapWithKeys(fn($case) => [$case->value => $case->shortLabel()])
        ) }};

        return {
            openModal: false,
            participants: [],
            guest: { last_name: '', first_name: '', gender: '', class: '' },

            init() {
                this.loadParticipants();
            },
            async loadParticipants() {
                const res = await fetch(`/admin/events/${eventId}/participants/json`);
                const list = await res.json();
                this.participants = list.sort((a, b) => {
                    if (a.status !== b.status) return a.status === 'entry' ? -1 : 1;
                    return a.order - b.order;
                });
            },
            async addGuest() {
                if (!this.guest.last_name || !this.guest.first_name || !this.guest.gender) {
                    alert('未入力の項目があります');
                    return;
                }
                const currentEntryCount = this.participants.filter(e => e.status === 'entry').length;
                const status = currentEntryCount < maxParticipants ? 'entry' : 'waitlist';

                const res = await fetch(`/admin/events/${eventId}/participants`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ ...this.guest, status: status })
                });

                if (res.ok) window.location.reload();
            }
        }
    }
</script>
@endpush