@extends('user.layouts.app')

@section('title', '参加者一覧')

@section('content')
<div class="px-4">
    <div class="mb-4">
        <a href="{{ route('user.events.index') }}" class="text-blue-600 hover:underline">← イベント一覧に戻る</a>
    </div>

    <h2 class="text-2xl font-bold mb-2">参加者一覧</h2>
    <p class="text-gray-600 mb-6">イベント名：{{ $event->title }}</p>

    {{-- キャンセル待ちの順番を数えるための変数を用意 --}}
    @php $wlCount = 1; @endphp

    <div class="bg-white shadow rounded-xl overflow-hidden">
        <table class="min-w-full leading-normal">
            <thead>
                <tr class="bg-gray-100 border-b">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No.</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">氏名（アカウント名）</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">性別</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">クラス</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($participants as $index => $participant)
                    <tr class="border-b {{ $participant->status === 'waitlist' ? 'bg-orange-50' : '' }}">
                        <td class="px-5 py-3 text-sm font-bold">
                            @if($participant->status === 'entry')
                                {{-- エントリー確定者は通常の番号 --}}
                                {{ $index + 1 }}
                            @else
                                {{-- キャンセル待ちは WL-1, WL-2... --}}
                                <span class="text-orange-600">WL-{{ $wlCount++ }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-sm">
                            @if($participant->user)
                                {{ $participant->user->account_name }}
                            @else
                                {{ $participant->name }} <span class="text-xs text-gray-400">(ゲスト)</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-sm">{{ $participant->gender ?? '—' }}</td>
                        <td class="px-5 py-3 text-sm">
                            {{ ($participant->class instanceof \App\Enums\PlayerClass) ? $participant->class->shortLabel() : '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-5 text-center text-sm text-gray-500">
                            現在、参加者はいません。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection