@extends('admin.layouts.app')

@section('title', '新規イベント作成')

@section('content')
<h2 class="text-2xl font-bold mb-6">新規イベント作成</h2>
@if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<form id="event-form" action="{{ route('admin.events.confirm') }}" method="POST" class="bg-white p-6 rounded-lg shadow w-full max-w-lg">
    {{-- 共通パーツの読み込み --}}
    @include('admin.events.partials.form-fields', [
        'isLimited' => false, 
        'isReplicate' => false
    ])

    <div class="mt-6 flex gap-4">
        <button type="submit" class="bg-admin text-white px-6 py-2 rounded hover:bg-admin-dark">
            確認画面へ
        </button>
        <a href="{{ route('admin.events.index') }}" class="bg-gray-400 text-white px-6 py-2 rounded">キャンセル</a>
    </div>
</form>

@include('admin.events.partials.form-scripts')
@endsection
