@extends('user.layouts.app')

@section('title', '参加者一覧')

@section('content')
<div class="px-4">
    <div class="mb-4">
        <a href="{{ route('user.events.index') }}" class="text-blue-600 hover:underline">← イベント一覧に戻る</a>
    </div>

    <h2 class="text-2xl font-bold mb-2">参加者一覧</h2>
    <p class="text-gray-600 mb-6">イベント名：{{ $event->title }}</p>

    @php
        // 1. エントリー（確定）とキャンセル待ちを分離して並び替える
        $entries = $participants->where('status', 'entry')->values();
        $waitlists = $participants->where('status', 'waitlist')->values();
        
        // 2. 確定枠 -> キャンセル待ちの順で結合
        $sortedParticipants = $entries->concat($waitlists);

        // 番号カウント用の変数
        $entryNo = 1;
        $wlNo = 1;
    @endphp

    <div class="bg-white shadow rounded-xl overflow-hidden">
        <table class="min-w-full leading-normal">
            <thead>
                <tr class="bg-gray-100 border-b">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No.</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">アカウント名</th>
                    {{-- 性別カラムを削除 --}}
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">クラス</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sortedParticipants as $participant)
                    <tr class="border-b {{ $participant->status === 'waitlist' ? 'bg-orange-50' : '' }}">
                        <td class="px-5 py-3 text-sm font-bold">
                            @if($participant->status === 'entry')
                                {{ $entryNo++ }}
                            @else
                                <span class="text-orange-600">WL-{{ $wlNo++ }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-sm">
                            {{-- 女性なら text-pink-700 を適用 --}}
                            <div class="font-bold {{ $participant->gender === '女性' ? 'text-pink-700' : 'text-gray-800' }}">
                                @php
                                    // 代表者（最初のメンバー）を取得
                                    $member = $participant->members->first();
                                @endphp
                                @if($member && $member->user)
                                    {{-- ユーザー登録がある場合はアカウント名を表示 --}}
                                    {{ $member->user->account_name ?? $member->full_name }}
                                @elseif($member)
                                    {{-- ゲスト等の場合は氏名を表示 --}}
                                    {{ $member->full_name }}
                                    <span class="ml-1 text-[10px] px-1.5 py-0.5 bg-gray-100 text-gray-500 rounded border border-gray-200 font-normal">ゲスト</span>
                                @else
                                    <span class="text-gray-400">不明</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3 text-sm">
                            @php
                                $member = $participant->members->first();
                                $classValue = $member ? $member->class : null;
                                // Enum型であることを考慮して表示
                                $classEnum = $classValue instanceof \App\Enums\PlayerClass 
                                    ? $classValue 
                                    : \App\Enums\PlayerClass::tryFrom($classValue);
                            @endphp
                            {{ $classEnum ? $classEnum->shortLabel() : ($classValue ?? '—') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-5 py-5 text-center text-sm text-gray-500">
                            現在、参加者はいません。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection