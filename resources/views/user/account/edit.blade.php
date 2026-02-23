@extends('user.layouts.app')

@section('title', 'プロフィール編集')

@section('content')
{{-- 郵便番号自動入力スクリプト --}}
<script src="https://yubinbango.github.io/yubinbango/yubinbango.js" charset="UTF-8"></script>

<div class="max-w-2xl mx-auto px-4">
    <div class="bg-white shadow rounded-xl overflow-hidden p-6">
        <h2 class="text-xl font-bold mb-6 flex items-center border-b pb-2">
            <span class="material-symbols-outlined mr-2">edit</span>
            プロフィール編集
        </h2>

        {{-- アラート表示 --}}
        @include('user.account._alerts')

        <form action="{{ route('user.account.update') }}" method="POST" class="h-adr space-y-6">
            @csrf
            @method('PATCH')
            
            {{-- 共通入力項目 (新規登録と共有) --}}
            @include('user.account._fields')

            <hr class="my-8">

            {{-- 編集画面のみ：メールアドレス (変更モーダル付き) --}}
            @include('user.account._email_section')

            {{-- 編集画面のみ：パスワード設定 --}}
            @include('user.account._password_section')

            {{-- 編集画面のみ：LINE連携 --}}
            @include('user.account._line_section')
            
            {{-- 編集画面のみ：通知設定 --}}
            @include('user.account._notification_settings')

            {{-- ボタンエリア --}}
            <div class="mt-8 pt-6 border-t flex items-center gap-4">
                <button type="submit" class="bg-user hover:opacity-90 text-white font-bold py-2 px-8 rounded-full shadow-md transition-all">
                    更新する
                </button>
                @if($user->last_name)
                    <a href="{{ route('user.account.show') }}" class="text-gray-500 text-sm">＜ 戻る</a>
                @else
                    <span class="text-red-500 text-xs font-bold">※ プロフィールの初期設定を完了させてください</span>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- モーダル --}}
@include('user.account._email_modal')

{{-- 隠しフォーム --}}
<form id="line-disconnect-form" action="{{ route('user.line.disconnect') }}" method="POST" class="hidden">
    @csrf @method('POST') 
</form>

@include('user.account._scripts')
@endsection
