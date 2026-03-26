@extends('user.layouts.app')
@section('title', 'プロフィール編集')

@section('content')
    {{-- yubinbango.js の読み込み --}}
    <script src="https://yubinbango.github.io/yubinbango/yubinbango.js" charset="UTF-8"></script>

    <div class="max-w-3xl mx-auto px-4 py-6">
        <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden">
            {{-- ヘッダー：管理者側と揃えたモダンなデザイン --}}
            <div class="border-b border-gray-100 px-8 py-6 bg-gray-50/50">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <span class="material-symbols-outlined mr-2 text-user text-3xl">edit_note</span>
                    プロフィール編集
                </h2>
            </div>

            <div class="p-8">
                {{-- アラート表示 --}}
                @include('user.account._alerts')

                <form action="{{ route('user.account.update') }}" method="POST" class="h-adr space-y-10">
                    @csrf
                    @method('PATCH')

                    {{-- 1. 基本情報セクション --}}
                    <section class="space-y-6">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center">
                            <span class="w-1.5 h-1.5 bg-user rounded-full mr-2"></span>
                            基本情報
                        </h3>

                        {{-- 氏名・性別・生年月日などは include 内で適切な余白・グリッドになっている想定 --}}
                        @include('user.account._fields', ['user' => $user])
                    </section>

                    <hr class="border-gray-100">

                    {{-- 2. 通知設定セクション --}}
                    <section class="space-y-6">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center text-user">
                            <span class="material-symbols-outlined text-sm mr-1">notifications_active</span>
                            通知設定
                        </h3>

                        <div class="bg-gray-50/80 p-6 rounded-2xl border border-gray-100">
                            @include('user.account._notification_settings')
                        </div>
                    </section>

                    {{-- ボタンエリア：管理者側と同じレイアウト --}}
                    <div
                        class="flex flex-col sm:flex-row items-center justify-between border-t border-gray-100 pt-10 gap-4">
                        <a href="{{ route('user.account.show') }}"
                            class="text-sm font-bold text-gray-400 hover:text-gray-600 transition flex items-center order-2 sm:order-1">
                            <span class="material-symbols-outlined text-base mr-1">arrow_back</span>
                            変更せずに戻る
                        </a>
                        <button type="submit"
                            class="w-full sm:w-auto bg-user hover:opacity-90 text-white px-10 py-3 rounded-full font-bold shadow-lg shadow-user/20 transition-all transform hover:-translate-y-0.5 active:translate-y-0 order-1 sm:order-2 text-center">
                            設定を保存する
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
