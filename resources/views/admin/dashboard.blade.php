<!-- resources/views/admin/dashboard.blade.php -->
@extends('admin.layouts.app')

@section('title', 'ダッシュボード')

@section('content')
    <h2 class="text-2xl font-bold mb-6">ようこそ、管理者ページへ</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="{{ route('admin.events.index') }}" class="p-6 bg-white shadow rounded-lg hover:bg-green-50">
            <h3 class="text-lg font-semibold mb-2">🎯 イベント管理</h3>
            <p>登録済みイベントの確認・編集ができます。</p>
        </a>

        <a href="{{ route('admin.events.create') }}" class="p-6 bg-white shadow rounded-lg hover:bg-green-50">
            <h3 class="text-lg font-semibold mb-2">🆕 新規イベント作成</h3>
            <p>新しいイベントを登録します。</p>
        </a>
    </div>
@endsection
