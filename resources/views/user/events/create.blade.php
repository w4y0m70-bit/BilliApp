@extends('user.layouts.app')

@section('title', 'エントリー情報の入力')

@section('content')
<div class="bg-white shadow rounded-lg p-6 max-w-lg mx-auto">
    <p class="text-sm text-gray-600">【 {{ $event->organizer->name ?? '主催者不明' }} 】</p>
    <h2 class="text-xl font-bold mb-4">{{ $event->title }}</h2>

    <form action="{{ route('user.entries.entry', $event->id) }}" method="POST" class="space-y-6">
        @csrf

        {{-- 1. クラス選択（主催者が選んだものだけを表示） --}}
        @php
        // すでにEnumオブジェクトである可能性が高いので、直接代入
        $userClass = auth()->user()->class;

        // もしEnumオブジェクトでなければ（文字列なら）変換を試みる
        if (!$userClass instanceof \App\Enums\PlayerClass) {
            $userClass = \App\Enums\PlayerClass::tryFrom($userClass);
        }
        
        $defaultClass = null;
        if ($userClass && $event->eventClasses->isNotEmpty()) {
            $userRank = $userClass->rank();
            
            $bestMatch = $event->eventClasses->map(function($item) use ($userRank) {
                // ここも同様に、取得した値が文字列であることを前提に変換
                $classEnum = \App\Enums\PlayerClass::tryFrom($item->class_name);
                $rank = $classEnum ? $classEnum->rank() : 0;
                return [
                    'class_name' => $item->class_name,
                    'distance' => abs($rank - $userRank),
                    'rank' => $rank
                ];
            })
            ->sortBy([
                ['distance', 'asc'],
                ['rank', 'desc']
            ])
            ->first();

            $defaultClass = $bestMatch['class_name'] ?? null;
        }
    @endphp

        <div>
            <div class="flex items-center mb-2">
                <label class="block font-bold mb-2">クラスの申告</label>
                <x-help help-key="user.entries.class" />
            </div>
            <div class="grid grid-cols-4 gap-3">
                @foreach($event->eventClasses as $class)
                    <label class="flex items-center gap-2 p-3 border rounded cursor-pointer hover:bg-gray-50 has-[:checked]:border-user has-[:checked]:bg-user/5">
                        <input type="radio" name="class" value="{{ $class->class_name }}" required
                            {{ old('class', $defaultClass) == $class->class_name ? 'checked' : '' }}>
                        <span class="font-medium">{{ $class->class_name }}</span>
                    </label>
                @endforeach
            </div>
            @error('class') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- 2. 伝達事項（主催者が設定している場合のみ表示） --}}
        @if($event->instruction_label)
            <div>
                <label class="block font-bold mb-1">{{ $event->instruction_label }}</label>
                <textarea name="user_answer" rows="3" 
                    class="w-full border rounded p-2 focus:ring-user focus:border-user"
                    placeholder="こちらに入力してください">{{ old('user_answer') }}</textarea>
                @error('user_answer') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        @endif

        {{-- 3. キャンセル待ち設定（満員の場合のみ自動で表示される等の制御も可） --}}
        @if($event->entry_count >= $event->max_participants)
            <div class="bg-yellow-50 p-3 rounded text-sm text-yellow-800">
                現在満員のため、<strong>キャンセル待ち</strong>としての登録となります。
            </div>
        @endif

        <div class="flex flex-col gap-3 pt-4 border-t">
            <button type="submit" class="w-full bg-user text-white py-3 rounded-lg font-bold hover:bg-user-dark transition">
                この内容でエントリーする
            </button>
            <a href="{{ route('user.events.show', $event->id) }}" class="text-center text-user text-sm underline">
                戻る
            </a>
        </div>
    </form>
</div>
@endsection