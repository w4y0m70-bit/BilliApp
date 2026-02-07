@extends('admin.layouts.app')

@section('title', $isReplicate ? 'イベント複製' : 'イベント編集')

@section('content')
<h2 class="text-2xl font-bold mb-6">{{ $isReplicate ? 'イベント複製' : 'イベント編集' }}</h2>
{{-- ★バリデーションエラーの表示エリア --}}
@if ($errors->any())
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 shadow" role="alert">
        <p class="font-bold">入力内容に不備があります：</p>
        <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- 
  1. action は常に confirm に飛ばす
  2. メソッドは常に POST 
--}}
<form id="event-form" action="{{ route('admin.events.confirm') }}" method="POST" class="bg-white p-6 rounded-lg shadow w-full max-w-lg">
    @csrf
    {{-- @method($formMethod) は不要なので消すかコメントアウト --}}

    @if(isset($event->id) && !($isReplicate ?? false))
        <input type="hidden" name="id" value="{{ $event->id }}">
    @endif

    {{-- 共通フィールド --}}
    @include('admin.events.partials.form-fields')

    <div class="mt-6 flex gap-4">
        <button type="submit" class="bg-admin text-white px-6 py-2 rounded hover:bg-admin-dark">
            確認画面へ進む
        </button>
        <a href="{{ route('admin.events.index') }}" class="bg-gray-400 text-white px-6 py-2 rounded">キャンセル</a>
    </div>
</form>

<!-- 削除ボタン -->
@php
    $now = now();
    // 公開日時が設定されており、かつ現在時刻がその公開日時を過ぎているか判定
    $alreadyPublished = $event->published_at && $event->published_at <= $now;
@endphp
@if(isset($event->id) && !$isReplicate && !$alreadyPublished)
    <div class="mt-4 p-4">
        <!-- <h3 class="text-red-600 font-bold mb-2">このイベントを削除する</h3>
        <p class="text-sm text-gray-600 mb-4">一度削除したイベントは元に戻せません。</p> -->
        
        <form action="{{ route('admin.events.destroy', $event->id) }}" method="POST" onsubmit="return confirm('本当に削除してもよろしいですか？');">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-500 text-white px-6 py-2 rounded hover:bg-red-700 transition">
                イベントを完全に削除する
            </button>
            <p class="text-sm text-gray-600 py-2 mb-4">一度削除したイベントは元に戻せません。<br>（未公開イベントのチケットは返却されます）</p>
        </form>
    </div>
@endif

@include('admin.events.partials.form-scripts')
@endsection