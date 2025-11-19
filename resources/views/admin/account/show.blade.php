@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow p-6 rounded">
    <h2 class="text-xl font-bold mb-4">アカウント情報</h2>

    <p><strong>店舗名：</strong>{{ $admin->name }}</p>
    <p><strong>住所：</strong>{{ $admin->address }}</p>
    <p><strong>電話番号：</strong>{{ $admin->phone }}</p>
    <p><strong>メール：</strong>{{ $admin->email }}</p>
    <p><strong>サブスク期限：</strong>{{ $admin->subscription_until }}</p>
    <p><strong>最終ログイン：</strong>{{ $admin->last_login_at }}</p>

    <a href="{{ route('admin.account.edit') }}" 
       class="mt-4 inline-block bg-admin text-white px-4 py-2 rounded">
        編集する
    </a>
</div>
@endsection
