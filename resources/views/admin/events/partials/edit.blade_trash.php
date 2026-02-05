@extends('admin.layouts.app')

@section('title', $isReplicate ? 'イベント複製' : 'イベント編集')

@section('content')
<h2 class="text-2xl font-bold mb-6">{{ $isReplicate ? 'イベント複製' : 'イベント編集' }}</h2>

<form id="event-form" action="{{ $formAction }}" method="POST" class="bg-white p-6 rounded-lg shadow w-full max-w-lg">
    @method($formMethod) {{-- $formMethod は Controller から 'PUT' または 'POST' を渡す --}}
    
    {{-- 共通パーツの読み込み --}}
    @include('admin.events.form')

    <div class="mt-6 flex gap-4">
        <button type="submit" class="bg-admin text-white px-6 py-2 rounded hover:bg-admin-dark">
            {{ $isReplicate ? 'この内容で作成' : '更新する' }}
        </button>
        <a href="{{ route('admin.events.index') }}" class="bg-gray-400 text-white px-6 py-2 rounded">キャンセル</a>
    </div>
</form>

{{-- 削除ボタン（編集モードかつ制限がない場合のみ表示など、適宜調整） --}}
@if(!$isReplicate)
    <div class="mt-6 border-t pt-4">
        <form action="{{ route('admin.events.destroy', $event->id) }}" method="POST" onsubmit="return confirm('本当に削除しますか？');">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded">このイベントを削除する</button>
        </form>
    </div>
@endif

@include('admin.events.partials.form-scripts')
@endsection