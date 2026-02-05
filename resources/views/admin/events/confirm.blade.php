@extends('admin.layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">イベント作成内容の確認</h1>

    <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
        <div class="bg-gray-50 px-6 py-3 border-b">
            <h2 class="text-lg font-semibold text-gray-700">入力内容</h2>
        </div>
        
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- イベント名 --}}
            <div class="col-span-1 md:col-span-2">
                <label class="block text-sm font-medium text-gray-500">イベント名</label>
                <div class="mt-1 text-lg text-gray-900 border-b pb-1">{{ $data['title'] ?? '' }}</div>
            </div>

            {{-- チケット情報 (目立つように表示) --}}
            <div class="col-span-1 md:col-span-2 bg-blue-50 p-4 rounded-lg border border-blue-100">
                <label class="block text-sm font-medium text-blue-600">使用するチケット</label>
                <div class="mt-1 text-lg font-bold text-blue-900">
                    @if($selectedTicket)
                        {{ $selectedTicket->plan->display_name }} 
                        <span class="text-sm font-normal text-blue-700 ml-2">
                            （定員上限： {{ $selectedTicket->plan->max_capacity }} 名 ／ 期限： {{ $selectedTicket->expired_at->format('Y/m/d') }}）
                        </span>
                    @else
                        <span class="text-red-500">チケット情報が見つかりません</span>
                    @endif
                </div>
            </div>

            {{-- 開催日時 --}}
            <div>
                <label class="block text-sm font-medium text-gray-500">開催日時</label>
                <div class="mt-1 text-gray-900">
                    {{ !empty($data['event_date']) ? \Carbon\Carbon::parse($data['event_date'])->locale('ja')->isoFormat('YYYY/MM/DD（ddd）HH:mm') : '—' }}
                </div>
            </div>

            {{-- エントリー締切 --}}
            <div>
                <label class="block text-sm font-medium text-gray-500">エントリー締切</label>
                <div class="mt-1 text-gray-900">
                    {{ !empty($data['entry_deadline']) ? \Carbon\Carbon::parse($data['entry_deadline'])->locale('ja')->isoFormat('YYYY/MM/DD（ddd）HH:mm') : '—' }}
                </div>
            </div>

            {{-- 公開日時 --}}
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">公開日時</label>
                <div class="mt-1 text-gray-900">
                    @if(!empty($data['published_at']))
                        {{-- 入力された日時を表示 --}}
                        <span>{{ !empty($data['published_at']) ? \Carbon\Carbon::parse($data['published_at'])->locale('ja')->isoFormat('YYYY/MM/DD（ddd）HH:mm') : '—' }}</span>

                        {{-- 現在時刻と比較して、過去（または現在）なら警告を出す --}}
                        @if(strtotime($data['published_at']) <= time())
                            <p class="text-red-500 text-xs mt-1 font-semibold">
                                ※設定日時が過去のため、登録後すぐに公開されます。
                            </p>
                        @endif
                    @else
                        {{-- 未入力（空）の場合 --}}
                        <span class="text-gray-500">即時公開（設定なし）</span>
                        <p class="text-red-500 text-xs mt-1 font-semibold">
                            ※即時公開されます。
                        </p>
                    @endif
                </div>
                {{-- 実際にstoreに送るためのhiddenデータ --}}
                <input type="hidden" name="published_at" value="{{ $data['published_at'] }}">
            </div>
            {{-- 最大人数 --}}
            <div>
                <label class="block text-sm font-medium text-gray-500">最大参加者数</label>
                <div class="mt-1 text-gray-900 font-bold">{{ $data['max_participants'] ?? '' }} 名</div>
            </div>

            {{-- キャンセル待ち --}}
            <div>
                <label class="block text-sm font-medium text-gray-500">キャンセル待ち</label>
                <div class="mt-1 text-gray-900">{{ ($data['allow_waitlist'] ?? false) == 1 ? '有' : '無' }}</div>
            </div>

            {{-- 募集クラス --}}
            <div>
                <label class="block text-sm font-medium text-gray-500">募集クラス</label>
                <div class="mt-1 flex flex-wrap gap-1">
                    @if(!empty($data['classes']))
                        @foreach($data['classes'] as $cls)
                            <span class="bg-gray-200 text-gray-800 text-xs px-2 py-1 rounded">{{ $cls }}</span>
                        @endforeach
                    @else
                        <span class="text-red-500">選択なし</span>
                    @endif
                </div>
            </div>

            {{-- 伝達事項 --}}
            <div>
                <label class="block text-sm font-medium text-gray-500">ユーザーへの追加質問</label>
                <div class="mt-1 text-gray-900">{{ $data['instruction_label'] ?: '（設定なし）' }}</div>
            </div>

            {{-- 説明文 --}}
            <div class="col-span-1 md:col-span-2">
                <label class="block text-sm font-medium text-gray-500">イベント説明</label>
                <div class="mt-1 text-gray-900 whitespace-pre-wrap border p-3 rounded bg-gray-50">{{ $data['description'] ?? '（なし）' }}</div>
            </div>
        </div>
    </div>

    {{-- アクションボタン --}}
    <div class="flex items-center space-x-4">
        @php
            // ★修正ポイント：複製フラグがある場合は、IDがあっても Update ではなく Store（新規作成）へ
            $isReplicate = !empty($data['is_replicate']) && $data['is_replicate'] == 1;
            $isUpdate = !empty($data['id']) && !$isReplicate;
            
            $formAction = $isUpdate ? route('admin.events.update', $data['id']) : route('admin.events.store');
        @endphp

        <form action="{{ $formAction }}" method="POST">
            @csrf
            
            @if($isUpdate)
                @method('PUT')
                <input type="hidden" name="id" value="{{ $data['id'] }}">
            @endif

            {{-- 複製フラグを hidden で引き継ぐ（Storeメソッドで必要になる場合があります） --}}
            @if($isReplicate)
                <input type="hidden" name="is_replicate" value="1">
            @endif

            {{-- すべてのデータを hidden で保持 --}}
            <input type="hidden" name="ticket_id" value="{{ $data['ticket_id'] }}">
            <input type="hidden" name="title" value="{{ $data['title'] }}">
            <input type="hidden" name="event_date" value="{{ $data['event_date'] }}">
            <input type="hidden" name="entry_deadline" value="{{ $data['entry_deadline'] }}">
            <input type="hidden" name="published_at" value="{{ $data['published_at'] }}">
            <input type="hidden" name="max_participants" value="{{ $data['max_participants'] }}">
            <input type="hidden" name="allow_waitlist" value="{{ $data['allow_waitlist'] ?? 0 }}">
            <input type="hidden" name="description" value="{{ $data['description'] }}">
            <input type="hidden" name="instruction_label" value="{{ $data['instruction_label'] }}">

            @if(!empty($data['classes']))
                @foreach($data['classes'] as $class)
                    <input type="hidden" name="classes[]" value="{{ $class }}">
                @endforeach
            @endif

            <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-blue-700 transition">
                {{ $isUpdate ? 'この内容で更新する' : 'この内容で登録する' }}
            </button>
        </form>

        <button type="button" onclick="history.back()" class="bg-gray-300 text-gray-800 px-8 py-3 rounded-lg font-bold hover:bg-gray-400 transition">
            修正する
        </button>
    </div>
@endsection