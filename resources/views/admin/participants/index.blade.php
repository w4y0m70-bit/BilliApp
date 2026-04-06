@extends('admin.layouts.app')

@section('title', $event->title . ' 参加者一覧')

@section('content')
    <div class="px-4">
        <div x-data="participantManager({{ $event->id }}, {{ $event->max_entries ?? 0 }}, {{ $event->max_team_size }})" class="space-y-3">
            <div>
                <a href="{{ route('admin.events.index') }}" class="text-admin hover:text-gray-800 flex items-center">
                    <span class="material-icons">arrow_back</span>
                    <span>戻る</span>
                </a>

                <div class="flex justify-between items-center mb-1">
                    <h2 class="text-2xl font-bold">{{ $event->title }} の参加者一覧</h2>
                    <div class="flex flex-wrap gap-1">
                        @foreach ($event->requiredGroups as $group)
                            <span
                                class="inline-flex items-center text-[10px] px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-bold border border-blue-200 shadow-sm">
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
                        {{ $event->max_entries }} {{ $event->team_size_label }}

                        <span class="text-sm ml-2 text-gray-500">
                            （キャンセル待ち：
                            <span class="font-bold">
                                {{ $participants->where('status', 'waitlist')->count() }}
                            </span>
                            ）
                        </span>
                    </p>
                    @if (!$event->isPast())
                        <div class="flex items-center gap-1">
                            <x-help help-key="admin.participants.add_guest" />
                            <button type="button" @click="openModal = true"
                                class="bg-admin text-white px-3 py-1 rounded hover:bg-admin-dark flex items-center justify-center">
                                <span class="material-icons text-lg">add</span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <div class="overflow-x-auto">
                <div id="participant-table-container">
                    <x-event.participant-list :event="$event" :participants="$participants" :max-entries="$event->max_entries" mode="admin" />
                </div>
            </div>

            {{-- モーダル内フォーム --}}
            <div x-show="openModal" x-cloak
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50">
                <div @click.away="openModal = false"
                    class="bg-white rounded-lg p-6 w-full max-w-2xl shadow-lg max-h-[90vh] overflow-y-auto">
                    <h3 class="text-xl font-bold mb-4">ゲストエントリー登録（チーム）</h3>

                    {{-- モーダル内フォーム --}}
                    <form @submit.prevent="addGuest">
                        <div class="mb-4">
                            <label class="block mb-1 font-bold text-admin text-sm">チーム名（任意）</label>
                            <input type="text" x-model="guest.team_name" class="border rounded w-full px-3 py-2"
                                placeholder="チーム◯◯">
                        </div>

                        <div class="space-y-4 border-t pt-4">
                            {{-- 最初から max_team_size 分の入力欄が表示される --}}
                            <template x-for="(member, index) in guest.members" :key="index">
                                <div class="p-3 bg-gray-50 rounded border border-gray-200">
                                    <p class="text-xs font-bold text-gray-500 mb-2" x-text="'メンバー ' + (index + 1)"></p>

                                    <div class="grid grid-cols-2 gap-3 mb-2">
                                        <input type="text" x-model="member.last_name"
                                            class="border rounded px-2 py-1 text-sm" placeholder="姓" required>
                                        <input type="text" x-model="member.first_name"
                                            class="border rounded px-2 py-1 text-sm" placeholder="名" required>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <select x-model="member.gender" class="border rounded px-2 py-1 text-sm" required>
                                            <option value="男性">男性</option>
                                            <option value="女性">女性</option>
                                            <option value="未回答">回答しない</option>
                                        </select>
                                        <select x-model="member.class" class="border rounded px-2 py-1 text-sm">
                                            <option value="">クラス選択</option>
                                            @foreach (\App\Enums\PlayerClass::cases() as $classOption)
                                                <option value="{{ $classOption->value }}">{{ $classOption->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="flex justify-end gap-3 mt-6 border-t pt-4">
                            <button type="button" @click="openModal=false"
                                class="px-4 py-2 rounded border text-sm">閉じる</button>
                            <button type="submit"
                                class="bg-admin text-white px-6 py-2 rounded font-bold text-sm">登録する</button>
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
            function participantManager(eventId, maxEntries, maxTeamSize) { // maxParticipantsからmaxEntries(チーム枠)へ
                return {
                    openModal: false,
                    participants: [],
                    guest: {
                        team_name: '',
                        members: []
                    },

                    init() {
                        this.resetGuestForm();
                        // this.loadParticipants();
                    },

                    resetGuestForm() {
                        const size = parseInt(maxTeamSize);
                        // イベントの規定人数分、空のオブジェクトを作成する
                        const initialMembers = [];
                        for (let i = 0; i < maxTeamSize; i++) {
                            initialMembers.push({
                                last_name: '',
                                first_name: '',
                                gender: '男性',
                                class: ''
                            });
                        }
                        this.guest = {
                            team_name: '',
                            members: initialMembers
                        };
                    },

                    async addGuest() {
                        // 枠数チェック
                        const currentCount = this.participants.filter(e => e.status === 'entry').length;
                        const status = currentCount < maxEntries ? 'entry' : 'waitlist';

                        const res = await fetch(`/admin/events/${eventId}/participants`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            },
                            body: JSON.stringify({
                                team_name: this.guest.team_name,
                                status: status,
                                members: this.guest.members
                            })
                        });

                        if (res.ok) {
                            window.location.reload();
                        } else {
                            const errorData = await res.json();
                            alert('エラーが発生しました: ' + (errorData.message || '不明なエラー'));
                        }
                    }
                }
            }
        </script>
    @endpush
