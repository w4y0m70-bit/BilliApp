<x-guest-layout>
<div class="min-h-screen bg-gray-50 py-12 px-4">
    <div class="max-w-2xl mx-auto bg-white shadow-md rounded-2xl p-8 text-gray-700">
        <div class="text-center mb-10">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">アップデート履歴</h1>
            <!-- <p class="text-sm text-gray-500">Billents の歩みと更新情報です</p> -->
        </div>

        <div class="space-y-10 relative">
            {{-- 縦線（タイムライン風のデザイン） --}}
            <div class="absolute left-3 top-2 bottom-0 w-0.5 bg-gray-200"></div>

            {{-- 最新のバージョン --}}
            <div class="relative pl-10">
                <div class="absolute left-0 top-1.5 w-6 h-6 bg-blue-500 rounded-full border-4 border-white shadow"></div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="font-mono font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded text-sm">
                        <v0 class="93"></v0>v0.94-beta</span>
                    <span class="text-sm text-gray-400">2026.02.10</span>
                </div>
                <h3 class="font-bold text-gray-800 mb-2">ベータ版公開</h3>
                <!-- <ul class="list-disc ml-5 space-y-1 text-sm leading-relaxed text-gray-600"> -->
                    <!-- <li>緊急メンテナンス時に「目安時間」を表示できるようになりました</li>
                    <li>ご利用者様向けに「ベータ版についてのお約束（規約）」を作成しました</li>
                    <li>サーバーの耐久テストを実施し、多人数アクセスへの準備を整えました</li> -->
                <!-- </ul> -->
            </div>

            {{-- 過去のバージョン --}}
            <!-- <div class="relative pl-10">
                <div class="absolute left-0 top-1.5 w-6 h-6 bg-gray-300 rounded-full border-4 border-white shadow"></div>
                <div class="flex items-center gap-3 mb-2 text-gray-400">
                    <span class="font-mono font-bold px-2 py-0.5 rounded text-sm bg-gray-100">v0.90-beta</span>
                    <span class="text-sm">2026.01.25</span>
                </div>
                <h3 class="font-bold text-gray-700 mb-2 text-sm">管理者向け機能の充実</h3>
                <ul class="list-disc ml-5 space-y-1 text-xs leading-relaxed text-gray-500">
                    <li>「いつ、誰が操作したか」を管理者が確認できるログ機能を追加しました</li>
                    <li>管理者画面の使い勝手を向上させました</li>
                </ul>
            </div> -->

            {{-- 最初のバージョン --}}
            <!-- <div class="relative pl-10">
                <div class="absolute left-0 top-1.5 w-6 h-6 bg-gray-200 rounded-full border-4 border-white shadow"></div>
                <div class="flex items-center gap-3 mb-2 text-gray-400">
                    <span class="font-mono font-bold px-2 py-0.5 rounded text-sm bg-gray-100">v0.1.0-beta</span>
                    <span class="text-sm">2026.01.10</span>
                </div>
                <h3 class="font-bold text-gray-700 mb-2 text-sm">プロジェクト開始</h3>
                <p class="text-xs text-gray-500 leading-relaxed">
                    Billents の開発をスタート。プレイヤーログインとイベント管理の基本機能を実装しました。
                </p>
            </div> -->
        </div>

        <div class="mt-12 pt-6 border-t text-center">
            <a href="{{ url('/') }}" class="inline-block text-blue-500 hover:text-blue-700 text-sm">トップページへ戻る</a>
        </div>
    </div>
</div>
</x-guest-layout>