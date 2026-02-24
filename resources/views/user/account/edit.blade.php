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
            
            {{-- 氏名・性別・生年月日・住所・電話番号・クラスなど --}}
            @include('user.account._fields')

            {{-- 通知設定も「アクション」ではなく「項目の選択」なので、ここに残すのが自然です --}}
            <hr class="my-8 border-gray-100">
            @include('user.account._notification_settings')

            <div class="mt-8 pt-6 border-t flex items-center gap-4">
                <button type="submit" class="bg-user hover:opacity-90 text-white font-bold py-2 px-10 rounded-full shadow-md transition-all">
                    設定を保存する
                </button>
                <a href="{{ route('user.account.show') }}" class="text-gray-500 text-sm">キャンセル</a>
            </div>
        </form>
    </div>
</div>

@endsection
