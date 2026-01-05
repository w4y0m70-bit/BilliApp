<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100 text-gray-800">
        <div class="max-w-2xl text-center">
            <h1 class="text-5xl font-bold mb-4">Billents</h1>
            <p class="mb-10 text-gray-600">
                イベントの管理・エントリーを24時間おまかせ
            </p>

            <div class="flex flex-col gap-8 mb-6">
                {{-- ユーザー --}}
                @if(auth('web')->check())
                <div class="flex items-center justify-center">
                <a href="{{ route('user.events.index') }}" class="bg-user text-white px-6 py-3 w-60 rounded-lg hover:bg-user-dark">
                    プレイヤーページへ</a>
                <x-help help-key="user.home" />
                </div>
                @else
                    <div class="flex items-center justify-center">
                        <a href="{{ route('user.login') }}"
                        class="bg-user text-white px-6 py-3 w-60 rounded-lg hover:bg-user-dark">
                            プレイヤーログイン
                        </a>

                        <x-help help-key="user.login" />
                    </div>
                @endif
                
                {{-- 管理者 --}}
                @if(auth('admin')->check())
                <div class="flex items-center justify-center">
                    <a href="{{ route('admin.home') }}" class="bg-admin text-white px-6 py-3 w-60 rounded-lg hover:bg-admin-dark">
                        管理者ページへ</a>
                    <x-help help-key="admin.home" />
                </div>
                @else
                <div class="flex items-center justify-center">
                    <a href="{{ route('admin.login') }}" 
                    class="bg-admin text-white px-6 py-3 w-60 rounded-lg hover:bg-admin-dark">
                        管理者ログイン
                    </a>
                    <x-help help-key="admin.login" />
                </div>
                @endif
                <!-- スコアボード（準備中） -->
                <!-- <a href=""
                   class="bg-yellow-400 text-black px-6 py-3 rounded-lg hover:bg-yellow-500 mt-6">
                   スコアボード（準備中）
                </a> -->
            </div>
            <div class="text-sm mb-1 text-gray-600 flex justify-center items-center gap-1">
                <span>Billentsについて</span>
                <x-help help-key="app.about" />
            </div>
            <p class="text-sm mb-10 text-gray-600">
            ©2025 Billents / ver 0.75-beta<br>
            </p>
        </div>
    </div>
</x-guest-layout>
