@extends('user.layouts.app')

@section('title', 'パスワード変更')

@section('content')
<div class="max-w-xl mx-auto px-4 py-8">
    <div class="bg-white shadow rounded-xl overflow-hidden">
        <div class="bg-user px-6 py-4">
            <h2 class="text-xl font-bold text-white flex items-center">
                <span class="material-symbols-outlined mr-2">lock_reset</span>
                パスワード変更
            </h2>
        </div>

        <div class="p-6">
            {{-- エラー表示用 --}}
            @include('user.account._alerts')

            <form action="{{ route('user.account.password.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PATCH')

                {{-- パスワードがDBに存在するか（nullでないか）をチェック --}}
                @if (!empty(auth()->user()->password))
                    {{-- 設定済み：現在のパスワードを求める --}}
                    <x-form.input 
                        type="password" 
                        name="current_password" 
                        label="現在のパスワード" 
                        placeholder="現在のパスワードを入力してください" 
                        required 
                    />
                    <p class="text-xs text-gray-500 mt-1">※セキュリティのため、現在のパスワードの確認が必要です。</p>
                @else
                    {{-- 未設定：案内を表示 --}}
                    <div class="bg-amber-50 border border-amber-200 p-4 rounded-lg">
                        <p class="text-amber-800 text-sm flex items-center">
                            <span class="material-symbols-outlined mr-2 text-base text-amber-500">info</span>
                            現在パスワードが設定されていません。新しく設定してください。
                        </p>
                    </div>
                @endif

                <hr class="border-gray-100">

                {{-- 共通：新しいパスワード入力欄（ここには案内文を入れない） --}}
                @include('user.account._password_section')

                {{-- ボタンエリア --}}
                <div class="pt-6 flex flex-col sm:flex-row items-center gap-4">
                    <button type="submit" class="w-full sm:w-auto bg-user hover:opacity-90 text-white font-bold py-2.5 px-10 rounded-full shadow-md transition">
                        {{ !empty(auth()->user()->password) ? 'パスワードを更新する' : 'パスワードを設定する' }}
                    </button>
                    <a href="{{ route('user.account.show') }}" class="text-gray-500 text-sm hover:underline">
                        キャンセル
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection