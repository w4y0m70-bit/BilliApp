<div>
    <!-- 以下は仮ユーザのためのphp。後に削除 -->
    @php
    use App\Models\User;
    $user = User::first() ?? User::create(['name' => 'テストユーザー', 'email' => 'test@example.com']);
    $userEntry = $event->userEntries->where('user_id', $user->id)->first();
    @endphp
    <!-- ここまで削除 -->
     
    <h2 class="text-xl font-bold mb-3">{{ $event->title }}</h2>

    {{-- イベント概要 --}}
    @if (!empty($event->description))
        <div class="mb-4">
            <h3 class="text-md font-semibold mb-1 text-gray-700">イベント内容</h3>
            <p class="text-gray-700 whitespace-pre-line">{{ $event->description }}</p>
        </div>
    @endif

    {{-- 開催情報 --}}
    <div class="text-sm text-gray-700 mb-4 space-y-1">
        <p><strong>開催日時：</strong>{{ $event->event_date->format('Y/m/d H:i') }}</p>
        <p><strong>エントリー締切：</strong>{{ $event->entry_deadline->format('Y/m/d H:i') }}</p>
        <p><strong>会場：</strong>{{ $event->venue ?? '未設定' }}</p>
        <p><strong>参加人数：</strong>
            {{ $event->entry_count }}／{{ $event->max_participants }}人
            （{{ $event->allow_waitlist ? $event->waitlist_count : '－' }}）
        </p>
    </div>

    {{-- 備考欄 --}}
    @if (!empty($event->notes))
        <div class="mb-4">
            <h3 class="text-md font-semibold mb-1 text-gray-700">備考</h3>
            <p class="text-gray-600 whitespace-pre-line">{{ $event->notes }}</p>
        </div>
    @endif

    {{-- エントリーボタン（状態に応じて切り替え） --}}
    @php
        // 仮ログイン中のユーザーID（本番では Auth::id() に変更）
        $user = \App\Models\User::first();
        $userEntry = $event->userEntries->where('user_id', $user->id)->first();
    @endphp

   {{-- エントリーボタン --}}
<div class="text-center">
    @php
        $userId = Auth::id() ?? 1;
        $userEntry = $event->userEntries->where('user_id', $userId)->first();
    @endphp

    @if ($userEntry && $userEntry->status === 'entry')
        {{-- ✅ エントリー中の場合：キャンセルボタン --}}
        <form action="{{ route('user.entries.cancel', $userEntry->id) }}" method="POST">
            @csrf
            <button 
                type="submit"
                class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition"
            >
                キャンセルする
            </button>
        </form>
    @elseif ($userEntry && $userEntry->status === 'waitlist')
        {{-- ⚠ キャンセル待ち中 --}}
        <p class="text-yellow-600 font-semibold">キャンセル待ち中です</p>
    @else
        {{-- 🟢 エントリー前 --}}
        <form action="{{ route('user.entries.entry', ['event' => $event->id]) }}" method="POST">
            @csrf
            <button 
                type="submit"
                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition"
            >
                このイベントにエントリーする
            </button>
        </form>
    @endif
</div>
</div>
