<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100 text-gray-800">
        <div class="max-w-2xl text-center">
            <h1 class="text-5xl font-bold mb-4">Billents</h1>
            <p class="mb-10 text-gray-600">
                イベントの管理・エントリーを24時間おまかせ
            </p>

            {{-- お知らせメッセージ表示欄 --}}
            @php
                $siteMessage = \App\Models\SiteMessage::find(1);
            @endphp

            @if($siteMessage && $siteMessage->is_active && $siteMessage->content)
                {{-- 
                    bg-gray-50: ごく薄いグレー
                    border-gray-200: 控えめな境界線
                    text-gray-600: 柔らかい文字色
                    w-96: 幅を約384pxに固定（max-w-smと同じくらいのサイズ）
                    max-w-full: スマホなどの画面幅が狭い時ははみ出さないように調整
                --}}
                <div class="mb-8 px-5 py-3 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-600 mx-auto max-w-sm max-w-full text-left shadow-sm">
                    <div class="flex items-center mb-1.5 font-semibold text-gray-500">
                        <svg class="w-3.5 h-3.5 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        お知らせ
                    </div>
                    <ul class="list-disc list-inside space-y-1 opacity-80">
                        @foreach(explode("\n", str_replace("\r", "", $siteMessage->content)) as $line)
                            @if(trim($line))
                                <li>{{ $line }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif

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
                
                {{-- 管理者セクション --}}
                <div class="flex items-center justify-center">
                    @if(auth('admin')->check())
                        {{-- ログイン済み（一般管理者でもマスターでも「管理者ページへ」と表示） --}}
                        <a href="{{ route('admin.events.index') }}" class="bg-admin text-white px-6 py-3 w-60 rounded-lg hover:bg-admin-dark text-center">
                            管理者ページへ
                        </a>
                        <x-help help-key="admin.events.index" />
                    @else
                        {{-- 未ログイン --}}
                        <a href="{{ route('admin.login') }}" class="bg-admin text-white px-6 py-3 w-60 rounded-lg hover:bg-admin-dark text-center">
                            管理者ログイン
                        </a>
                        <x-help help-key="admin.login" />
                    @endif
                </div>
                <!-- スコアボード（準備中） -->
                <!-- <a href=""
                   class="bg-yellow-400 text-black px-6 py-3 rounded-lg hover:bg-yellow-500 mt-6">
                   スコアボード（準備中）
                </a> -->
            </div>
            <div class="mt-12 pt-6 border-t border-gray-200">
                <div class="flex flex-wrap justify-center items-center gap-6 text-sm text-gray-500 mb-4">
                    {{-- 利用規約 --}}
                    <a href="{{ route('terms') }}" class="hover:text-blue-600 transition underline-offset-4 hover:underline">利用規約</a>
                    
                    {{-- アップデート履歴 --}}
                    <a href="{{ route('changelog') }}" class="hover:text-blue-600 transition underline-offset-4 hover:underline">アップデート履歴</a>
                    
                    {{-- LINEリンク --}}
                    <a href="{{ config('services.line.url') }}" target="_blank" 
                    class="inline-flex items-center gap-2 text-[#06C755] font-bold hover:opacity-80 transition group">
                        {{-- 
                            スマホ(デフォルト): 40px (w-10 h-10)
                            PC(sm以上): 24px (sm:w-6 sm:h-6) に切り替え
                        --}}
                        <img src="{{ asset('images/LINE_Brand_icon.png') }}" 
                            alt="LINEアイコン" 
                            class="w-10 h-10 sm:w-6 sm:h-6 object-contain">
                        
                        <span class="text-base sm:text-sm">Billents公式</span>
                    </a>
                </div>

                <div class="text-sm text-gray-400">
                    <div class="flex justify-center items-center gap-1 mb-1">
                        <span>Billentsについて</span>
                        <x-help help-key="app.about" />
                    </div>
                    <p>
                        ©2026 Billents / <span class="font-mono">ver 0.964-beta</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
