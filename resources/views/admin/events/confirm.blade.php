@extends('admin.layouts.app')

@section('title', 'イベント作成内容の確認')

@section('content')

    <x-form.section title="イベント作成内容の確認" type="admin" maxWidth="max-w-4xl">

        {{-- 表示用レイアウト --}}
        <div class="space-y-6">

            {{-- イベント名 --}}
            <div class="border-b pb-4">
                <label class="block text-sm font-medium text-gray-500">イベント名</label>
                <div class="mt-1 text-xl font-bold text-gray-900">{{ $data['title'] ?? '' }}</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                {{-- チケット情報 --}}
                <div class="col-span-1 md:col-span-2 bg-orange-50 p-4 rounded-lg border border-orange-100">
                    <label class="block text-sm font-medium text-admin">使用するチケット</label>
                    <div class="mt-1">
                        @if ($selectedTicket)
                            <span class="text-lg font-bold text-gray-900">{{ $selectedTicket->plan->display_name }}</span>
                            <span class="text-sm text-gray-600 ml-2">（定員上限：{{ $selectedTicket->plan->max_capacity }}
                                名）</span>
                            <p class="text-xs text-gray-500 mt-1">
                                チケット使用期限：{{ $selectedTicket->expired_at->format('Y/m/d') }}
                            </p>
                        @else
                            <span class="text-red-500 font-bold">チケット情報が見つかりません</span>
                        @endif
                    </div>
                </div>

                {{-- 開催日時 --}}
                <div class="bg-gray-50 p-3 rounded">
                    <label class="block text-sm font-medium text-gray-500">開催日時</label>
                    <div class="mt-1 text-gray-900 font-semibold">
                        {{ !empty($data['event_date']) ? \Carbon\Carbon::parse($data['event_date'])->locale('ja')->isoFormat('YYYY/MM/DD（ddd）HH:mm') : '—' }}
                    </div>
                </div>

                {{-- エントリー締切 --}}
                <div class="bg-gray-50 p-3 rounded">
                    <label class="block text-sm font-medium text-gray-500">エントリー締切</label>
                    <div class="mt-1 text-gray-900 font-semibold">
                        {{ !empty($data['entry_deadline']) ? \Carbon\Carbon::parse($data['entry_deadline'])->locale('ja')->isoFormat('YYYY/MM/DD（ddd）HH:mm') : '—' }}
                    </div>
                </div>

                {{-- 公開日時 --}}
                <div class="bg-gray-50 p-3 rounded">
                    <label class="block text-sm font-medium text-gray-500">公開日時</label>
                    <div class="mt-1 text-gray-900">
                        @if (!empty($data['published_at']))
                            <span
                                class="font-semibold">{{ \Carbon\Carbon::parse($data['published_at'])->locale('ja')->isoFormat('YYYY/MM/DD（ddd）HH:mm') }}</span>
                            @if (strtotime($data['published_at']) <= time())
                                <p class="text-red-500 text-[10px] mt-1">※登録後すぐに公開されます</p>
                            @endif
                        @else
                            <span class="text-admin font-bold text-sm italic">即時公開</span>
                        @endif
                    </div>
                </div>

                {{-- キャンセル待ち --}}
                <div class="bg-gray-50 p-3 rounded">
                    <label class="block text-sm font-medium text-gray-500">キャンセル待ち</label>
                    <div class="mt-1 text-gray-900 font-semibold">
                        {{ ($data['allow_waitlist'] ?? 0) == 1 ? '有（受け付ける）' : '無' }}
                    </div>
                </div>

                {{-- 募集枠数と人数 --}}
                <div class="col-span-1 md:col-span-2 grid grid-cols-3 gap-4 bg-gray-100 p-4 rounded-lg">
                    <div>
                        <label class="block text-[10px] font-medium text-gray-500 uppercase">募集枠数</label>
                        <div class="text-lg font-bold text-gray-900">{{ $data['max_entries'] ?? '' }} 枠</div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-medium text-gray-500 uppercase">1枠の構成</label>
                        <div class="text-lg font-bold text-gray-900">
                            {{ \App\Models\Event::getTeamTypeName($data['max_team_size'] ?? 1) }}
                        </div>
                    </div>
                    <div class="border-l border-gray-300 pl-4">
                        <label class="block text-[10px] font-medium text-admin uppercase">合計最大人数</label>
                        <div class="text-xl font-black text-admin">
                            {{ ($data['max_entries'] ?? 0) * ($data['max_team_size'] ?? 1) }} 名
                        </div>
                    </div>
                </div>

                {{-- 募集クラス --}}
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500 mb-2">対象クラス</label>
                    <div class="flex flex-wrap gap-1">
                        @if (!empty($data['classes']))
                            @foreach ($data['classes'] as $classValue)
                                @php
                                    $classEnum = \App\Enums\PlayerClass::tryFrom($classValue);
                                    $displayText = $classEnum ? $classEnum->shortLabel() : $classValue;
                                    $bgColor = $classEnum ? $classEnum->color() : 'bg-gray-500';
                                @endphp
                                <x-event.class-tag size="md" :bgColor="$bgColor">
                                    {{ $displayText }}
                                </x-event.class-tag>
                            @endforeach
                        @endif
                    </div>
                </div>

                {{-- 参加制限（グループ） ここに入る --}}

                {{-- 伝達事項 --}}
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500">ユーザーへの追加質問・伝達事項</label>
                    <div
                        class="mt-1 text-gray-900 p-3 bg-gray-50 rounded border min-h-[45px] whitespace-pre-wrap text-sm italic">
                        {{ $data['instruction_label'] ?: '（設定なし）' }}
                    </div>
                </div>

                {{-- 説明文 --}}
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500">イベント内容・詳細</label>
                    <div
                        class="mt-1 text-gray-900 whitespace-pre-wrap border p-4 rounded bg-gray-50 text-sm leading-relaxed min-h-[100px]">
                        {{ $data['description'] ?? '（未入力）' }}
                    </div>
                </div>
            </div>

            {{-- 送信・修正ボタン --}}
            <div class="mt-10 pt-6 border-t border-gray-100 flex flex-col sm:flex-row gap-4">
                @php
                    $isReplicate = !empty($data['is_replicate']) && $data['is_replicate'] == 1;
                    $isUpdate = !empty($data['id']) && !$isReplicate;
                    $formAction = $isUpdate ? route('admin.events.update', $data['id']) : route('admin.events.store');
                @endphp

                <form action="{{ $formAction }}" method="POST" class="w-full sm:w-auto">
                    @csrf
                    @if ($isUpdate)
                        @method('PUT')
                    @endif

                    {{-- 全データを hidden で保持 --}}
                    <input type="hidden" name="id" value="{{ $data['id'] ?? '' }}">
                    <input type="hidden" name="ticket_id" value="{{ $data['ticket_id'] ?? '' }}">
                    <input type="hidden" name="title" value="{{ $data['title'] ?? '' }}">
                    <input type="hidden" name="event_date" value="{{ $data['event_date'] ?? '' }}">
                    <input type="hidden" name="entry_deadline" value="{{ $data['entry_deadline'] ?? '' }}">
                    <input type="hidden" name="published_at" value="{{ $data['published_at'] ?? '' }}">
                    <input type="hidden" name="max_entries" value="{{ $data['max_entries'] ?? '' }}">
                    <input type="hidden" name="max_team_size" value="{{ $data['max_team_size'] ?? 1 }}">
                    <input type="hidden" name="allow_waitlist" value="{{ $data['allow_waitlist'] ?? 0 }}">
                    <input type="hidden" name="description" value="{{ $data['description'] ?? '' }}">
                    <input type="hidden" name="instruction_label" value="{{ $data['instruction_label'] ?? '' }}">
                    <input type="hidden" name="is_replicate" value="{{ $isReplicate ? 1 : 0 }}">

                    @if (!empty($data['classes']))
                        @foreach ($data['classes'] as $class)
                            <input type="hidden" name="classes[]" value="{{ $class }}">
                        @endforeach
                    @endif

                    <button type="submit"
                        class="w-full sm:w-auto bg-admin text-white px-10 py-3 rounded-lg font-bold hover:bg-admin-dark shadow-lg transition transform active:scale-95">
                        {{ $isUpdate ? 'この内容で更新を確定する' : 'この内容でイベントを登録する' }}
                    </button>
                </form>

                <a href="javascript:void(0)" onclick="history.back()"
                    class="w-full sm:w-auto bg-gray-400 text-white px-10 py-3 rounded-lg font-bold hover:bg-gray-500 transition text-center shadow-md flex items-center justify-center">
                    戻って修正する
                </a>
            </div>
        </div>

    </x-form.section>

@endsection

{{-- 参加制限（グループ） グループ実装済＞未公開【消さないこと！】 --}}
{{-- <div>
                <label class="block text-sm font-medium text-gray-500">参加制限（グループ限定）</label>
                <div class="mt-1 flex flex-wrap gap-1">
                    @if (!empty($data['groups']))
                        @foreach ($data['groups'] as $groupId)
                            @php
                                // IDからグループ名を取得（コントローラで $allGroups などを渡しておくとスムーズです）
                                $group = \App\Models\Group::find($groupId);
                            @endphp
                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded border border-blue-200">
                                {{ $group->name ?? '不明なグループ' }}
                            </span>
                        @endforeach
                    @else
                        <span class="text-gray-500 italic">制限なし（全員に公開）</span>
                    @endif
                </div>
            </div> --}}
