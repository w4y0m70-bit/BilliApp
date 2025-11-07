@extends('admin.layouts.app')

@section('title', '参加者一覧')

@section('content')
<h2 class="text-2xl font-bold mb-6">{{ $event->title }} の参加者一覧participants</h2>

<a href="{{ route('admin.events.index') }}" class="inline-block mb-4 text-gray-500 hover:underline">← イベント一覧へ戻る</a>

<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
    @forelse($participants as $p)
        <div class="bg-white p-4 rounded-lg shadow">
            <h3 class="font-semibold text-lg">{{ $p['name'] }}</h3>
            <p class="mt-1">
                ステータス: 
                @if($p['status'] === '参加')
                    <span class="text-green-600 font-medium">{{ $p['status'] }}</span>
                @else
                    <span class="text-red-600 font-medium">{{ $p['status'] }}</span>
                @endif
            </p>
        </div>
    @empty
        <p class="col-span-3 text-center text-gray-500">参加者はいません</p>
    @endforelse
</div>
@endsection
