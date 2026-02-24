@extends('admin.layouts.app')

@section('title', 'パスワード変更')

@section('content')
<div class="max-w-xl mx-auto px-4 py-8">
    <div class="bg-white shadow rounded-xl overflow-hidden">
        <div class="bg-admin px-6 py-4">
            <h2 class="text-xl font-bold text-white flex items-center">
                <span class="material-symbols-outlined mr-2">lock_reset</span>
                パスワード変更
            </h2>
        </div>

        <div class="p-6">
            {{-- エラー表示 --}}
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.account.password.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PATCH')

                @if ($admin->password)
                    <x-form.input 
                        type="password" 
                        name="current_password" 
                        label="現在のパスワード" 
                        placeholder="現在のパスワードを入力してください" 
                        required 
                    />
                    <p class="text-xs text-gray-500 mt-1">※セキュリティのため、現在のパスワードの確認が必要です。</p>
                @else
                    <div class="bg-amber-50 border border-amber-200 p-4 rounded-lg">
                        <p class="text-amber-800 text-sm flex items-center">
                            <span class="material-symbols-outlined mr-2 text-base">info</span>
                            現在パスワードが設定されていません。新しく設定してください。
                        </p>
                    </div>
                @endif

                <hr class="border-gray-100">

                {{-- 新しいパスワード入力（共通パーツがあれば include、なければ直書き） --}}
                <x-form.input 
                    type="password" 
                    name="password" 
                    label="新しいパスワード" 
                    required 
                />
                
                <x-form.input 
                    type="password" 
                    name="password_confirmation" 
                    label="新しいパスワード（確認）" 
                    required 
                />

                <div class="pt-6 flex flex-col sm:flex-row items-center gap-4">
                    <button type="submit" class="w-full sm:w-auto bg-admin hover:opacity-90 text-white font-bold py-2.5 px-10 rounded-full shadow-md transition text-center">
                        {{ $admin->password ? 'パスワードを更新する' : 'パスワードを設定する' }}
                    </button>
                    <a href="{{ route('admin.account.show') }}" class="text-gray-500 text-sm hover:underline">
                        キャンセル
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection