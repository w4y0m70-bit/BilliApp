@extends('admin.layouts.app')
@section('title', 'チケット')

@section('content')
<div class="px-4 py-2">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">チケット管理</h2>

    {{-- 1. コード入力セクション（デザイン微調整） --}}
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-2">
        <h3 class="text-sm font-bold text-gray-500 mb-4 uppercase tracking-wider">キャンペーンコード入力</h3>
        <form action="{{ route('admin.tickets.use_code') }}" method="POST" class="flex gap-2">
            @csrf
            <input type="text" name="code" class="form-control w-64 border-gray-300 rounded-lg" placeholder="コードを入力" required>
            <button type="submit" class="bg-admin text-white px-6 py-2 rounded-lg hover:bg-admin-dark transition">
                チケットを受け取る
            </button>
        </form>
    </div>

    {{-- 2. タブ切り替え（3タブ構成） --}}
    <div class="flex border-b border-gray-200 mb-6">
        <a href="{{ route('admin.tickets.index', ['tab' => 'ready']) }}" 
           class="px-6 py-2 font-medium {{ $tab === 'ready' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
            利用可能
        </a>
        <a href="{{ route('admin.tickets.index', ['tab' => 'active']) }}" 
           class="px-6 py-2 font-medium {{ $tab === 'active' ? 'border-b-2 border-orange-500 text-orange-500' : 'text-gray-500 hover:text-gray-700' }}">
            使用中
        </a>
        <a href="{{ route('admin.tickets.index', ['tab' => 'used']) }}" 
           class="px-6 py-2 font-medium {{ $tab === 'used' ? 'border-b-2 border-gray-600 text-gray-600' : 'text-gray-500 hover:text-gray-700' }}">
            使用済み履歴
        </a>
    </div>

    {{-- 3. チケット一覧 --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        {{-- A. 使用中のタブ（1枚ずつ表示） --}}
        @if($tab === 'active')
            @forelse($tickets as $ticket)
                <div class="relative flex bg-white rounded-xl shadow-sm border-l-4 border-l-orange-400 border-y border-r border-gray-200 overflow-hidden">
                    <div class="flex-grow p-5 border-r border-dashed border-gray-300 relative">
                        <div class="text-[10px] font-bold text-orange-500 uppercase mb-1">{{ $ticket->plan->display_name }}</div>
                        <div class="text-xl font-bold text-gray-800">{{ $ticket->plan->max_capacity }}名定員</div>
                        <div class="mt-2 p-2 bg-orange-50 rounded text-xs text-orange-700 font-semibold italic">
                            開催中: {{ $ticket->event->title ?? 'イベント' }}
                        </div>
                    </div>
                    <div class="w-24 flex flex-col items-center justify-center bg-orange-50 p-2 text-center">
                        <span class="text-[10px] font-bold text-orange-500 uppercase">In Use</span>
                        <div class="text-[10px] text-orange-600 mt-1 font-bold">使用中</div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 col-span-full py-10 text-center">現在使用中のチケットはありません。</p>
            @endforelse

        {{-- B. 利用可能 or 使用済みタブ（グループ表示） --}}
        @else
            @forelse($groupedTickets as $group)
                @php $first = $group->first(); @endphp
                <div class="relative flex bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    {{-- 枚数バッジ（右上に配置） --}}
                    @if($group->count() > 1)
                        <div class="absolute top-0 right-0 z-10">
                            <div class="bg-gray-800 text-white text-[10px] font-bold px-2 py-1 rounded-bl-lg">
                                × {{ $group->count() }}
                            </div>
                        </div>
                    @endif

                    <div class="flex-grow p-5 border-r border-dashed border-gray-300 relative">
                        <div class="text-[10px] font-bold {{ $tab === 'used' ? 'text-gray-400' : 'text-indigo-500' }} uppercase mb-1">
                            {{ $first->plan->display_name }}
                        </div>
                        <div class="text-xl font-bold {{ $tab === 'used' ? 'text-gray-400' : 'text-gray-800' }}">
                            {{ $first->plan->max_capacity }}名定員
                        </div>
                        <div class="text-[10px] mt-2 
                            {{ ($tab === 'ready' && $first->isUrgent()) ? 'text-red-600 font-bold' : 'text-gray-400' }}">
                            
                            {{-- 期限が近い場合のみ警告アイコンを表示 --}}
                            @if($tab === 'ready' && $first->isUrgent())
                                <span class="animate-pulse">⚠️</span>
                            @endif

                            {{ $tab === 'used' 
                                ? '開催日: ' . $first->event->event_date->format('Y/m/d') 
                                : '有効期限: ' . $first->expired_at->format('Y/m/d') 
                            }}
                        </div>
                    </div>

                    <div class="w-24 flex flex-col items-center justify-center bg-gray-50 p-2">
                        @if($tab === 'ready')
                            <span class="text-[10px] font-bold text-green-500 uppercase">Ready</span>
                            <a href="{{ route('admin.events.create', ['ticket_id' => $first->id]) }}" 
                               class="mt-2 bg-green-500 text-white text-[10px] px-3 py-1 rounded-full font-bold hover:bg-green-600 shadow-sm transition">
                                使う
                            </a>
                        @else
                            <span class="text-[10px] font-bold text-gray-400 uppercase">Used</span>
                            <div class="text-[10px] text-gray-400 mt-1 font-bold">使用済み</div>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-gray-500 col-span-full py-10 text-center">表示するチケットはありません。</p>
            @endforelse
        @endif
    </div>
</div>

{{-- 成功時のダイアログ --}}
@if (session('success_msg'))
    <script>
        window.onload = function() {
            alert("{{ session('success_msg') }}");
        };
    </script>
@endif

{{-- エラー時のダイアログ --}}
@if (session('error_msg'))
    <script>
        window.onload = function() {
            alert("{{ session('error_msg') }}");
        };
    </script>
@endif
@endsection