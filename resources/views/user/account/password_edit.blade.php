@extends('user.layouts.app')
@section('title', 'パスワード変更')

@section('content')
    <div class="max-w-xl mx-auto px-4 py-2">
        <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden">
            {{-- ヘッダー：共通デザイン --}}
            <div class="border-b border-gray-100 px-8 py-6 bg-gray-50/50">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <span class="material-symbols-outlined mr-2 text-user text-3xl">lock_reset</span>
                    パスワード変更
                </h2>
            </div>

            <div class="p-6">
                {{-- エラー表示（管理者側のスタイルに統一） --}}
                @if ($errors->any())
                    <div
                        class="mb-8 flex items-start bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl shadow-sm">
                        <span class="material-symbols-outlined mr-2 text-red-500">error</span>
                        <ul class="text-sm font-bold list-none">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('user.account.password.update') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    {{-- 現在のパスワード確認セクション --}}
                    <section>
                        @if (auth()->user()->password)
                            <div class="space-y-2">
                                <x-form.input type="password" name="current_password" label="現在のパスワード"
                                    placeholder="現在のパスワードを入力してください" required />
                                <p class="text-[11px] text-gray-400 flex items-center ml-1 font-bold italic">
                                    <span class="material-symbols-outlined text-[13px] mr-1 text-user/60">security</span>
                                    本人確認のため現在のパスワードが必要です
                                </p>
                            </div>
                        @else
                            <div class="bg-amber-50 border border-amber-200 p-4 rounded-xl flex items-center shadow-sm">
                                <span class="material-symbols-outlined mr-3 text-amber-500">info</span>
                                <p class="text-amber-800 text-xs font-bold leading-relaxed">
                                    現在パスワードが設定されていません。<br>
                                    セキュリティ向上のため、新しく設定してください。
                                </p>
                            </div>
                        @endif
                    </section>

                    <hr class="border-gray-100">

                    {{-- 新しいパスワード入力セクション --}}
                    <section class="space-y-6">
                        <div class="grid grid-cols-1 gap-6">
                            <x-form.input type="password" name="password" label="新しいパスワード" placeholder="8文字以上の半角英数字"
                                required />

                            <x-form.input type="password" name="password_confirmation" label="新しいパスワード（確認）"
                                placeholder="もう一度入力してください" required />
                        </div>
                    </section>

                    {{-- ボタンエリア：管理者側の洗練されたレイアウトを採用 --}}
                    <div
                        class="pt-10 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-gray-100">
                        {{-- 戻るボタンを order-2 (スマホで下) に配置 --}}
                        <a href="{{ route('user.account.show') }}"
                            class="text-sm font-bold text-gray-400 hover:text-user transition flex items-center order-2 sm:order-1">
                            <span class="material-symbols-outlined text-base mr-1">arrow_back</span>
                            変更せずに戻る
                        </a>

                        {{-- 送信ボタンを order-1 (スマホで上) に配置 --}}
                        <button type="submit"
                            class="w-full sm:w-auto bg-user hover:opacity-90 text-white px-10 py-3 rounded-full font-bold shadow-lg shadow-user/20 transition-all transform hover:-translate-y-0.5 active:translate-y-0 order-1 sm:order-2 text-center">
                            {{ auth()->user()->password ? 'パスワードを更新する' : 'パスワードを設定する' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
