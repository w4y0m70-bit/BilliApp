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
            <div class="mt-12 pt-6 border-t border-gray-200">
                <div class="flex justify-center gap-6 text-sm text-gray-500 mb-4">
                    {{-- 利用規約へのリンク --}}
                    <a href="{{ route('terms') }}" class="hover:text-blue-600 transition underline-offset-4 hover:underline">利用規約</a>
                    
                    {{-- アップデート履歴へのリンク --}}
                    <a href="{{ route('changelog') }}" class="hover:text-blue-600 transition underline-offset-4 hover:underline">アップデート履歴</a>
                    
                    {{-- お問い合わせ等（将来用） --}}
                    {{-- <a href="#" class="hover:text-blue-600 transition">お問い合わせ</a> --}}
                    {{-- LINEリンク --}}
                    <a href="{{ config('services.line.url') }}" target="_blank" class="text-[#06C755] hover:opacity-80 transition">
                        <svg class="w-5 h-5 inline" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 10.304c0-5.369-5.383-9.738-12-9.738-6.616 0-12 4.369-12 9.738 0 4.814 4.269 8.846 10.036 9.608.391.084.922.258 1.057.592.121.303.079.778.039 1.085l-.171 1.027c-.052.303-.242 1.186 1.039.647 1.281-.54 6.911-4.069 9.438-6.967 1.636-1.802 2.571-3.864 2.571-6.007zm-15.825 3.307c0 .195-.159.353-.354.353h-1.611c-.195 0-.353-.158-.353-.353v-3.325c0-.195.158-.353.353-.353h.161c.195 0 .353.158.353.353v2.812h1.1c.194 0 .353.158.353.353v.16zm2.348 0c0 .195-.158.353-.353.353h-.161c-.195 0-.353-.158-.353-.353v-3.325c0-.195.158-.353.353-.353h.161c.195 0 .353.158.353.353v3.325zm4.558 0c0 .195-.158.353-.354.353h-.161c-.194 0-.353-.158-.353-.353l-.001-1.894-1.111 1.77c-.125.197-.245.247-.341.247h-.163c-.195 0-.353-.158-.353-.353v-3.325c0-.195.158-.353.353-.353h.161c.195 0 .353.158.353.353v1.894l1.111-1.77c.125-.197.245-.247.341-.247h.162c.195 0 .354.158.354.353v3.325zm3.178-1.226c0 .195-.158.353-.353.353h-1.099v.713h1.099c.195 0 .353.158.353.353v.16c0 .195-.158.353-.353.353h-1.613c-.195 0-.353-.158-.353-.353v-3.325c0-.195.158-.353.353-.353h1.613c.195 0 .353.158.353.353v.16c0 .195-.158.353-.353.353h-1.099v.713h1.099c.195 0 .353.158.353.353v.16z"/>
                        </svg>
                        LINE公式
                    </a>
                </div>

                <div class="text-sm text-gray-400">
                    <div class="flex justify-center items-center gap-1 mb-1">
                        <span>Billentsについて</span>
                        <x-help help-key="app.about" />
                    </div>
                    <p>
                        ©2026 Billents / <span class="font-mono">ver 0.961-beta</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
