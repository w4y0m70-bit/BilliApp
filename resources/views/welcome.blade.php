<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100 text-gray-800">
        <div class="max-w-2xl text-center">
            <h1 class="text-4xl font-bold mb-6">🎱 ビリヤードエントリーシステム</h1>
            <p class="mb-10 text-gray-600">
                イベントのエントリーからスコアボード管理まで、これひとつで。
            </p>

            <div class="flex flex-col gap-4">
                @auth
                    @if(auth()->user()->role === 'player')
                        <a href="{{ route('events.index') }}" 
                           class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600">
                           イベント一覧へ
                        </a>
                    @elseif(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" 
                           class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600">
                           管理者ダッシュボードへ
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" 
                       class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600">
                       ユーザーログイン
                    </a>
                    <a href="{{ route('register') }}" 
                       class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600">
                       新規登録
                    </a>
                    <a href="{{ url('/admin/login') }}" 
                       class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600">
                       管理者ログイン
                    </a>
                @endauth

                <a href="{{ route('scoreboard') }}" 
                   class="bg-yellow-400 text-black px-6 py-3 rounded-lg hover:bg-yellow-500 mt-6">
                   スコアボード（準備中）
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
