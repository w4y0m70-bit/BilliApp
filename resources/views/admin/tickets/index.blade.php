@extends('admin.layouts.app')
@section('title', 'チケット')

@section('content')
<div class="px-4 py-2">
    <div class="flex items-center space-x-2">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">チケット管理<x-help help-key="admin.tickets.index" /></h2>
    </div>

    {{-- 1. コード入力セクション（デザイン微調整） --}}
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-2">
        <div class="flex items-center">
            <h3 class="text-sm font-bold text-gray-500 mb-4 uppercase tracking-wider">キャンペーンコード入力
            <x-help help-key="admin.tickets.use_code" /></h3>
        </div>
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
    <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @if($tab === 'active')
            @forelse($tickets as $ticket)
                <x-ticket :ticket="$ticket" tab="active" />
            @empty
                <p class="text-gray-500 col-span-full py-10 text-center">使用中のチケットはありません。</p>
            @endforelse

        @else
            @forelse($groupedTickets as $group)
                @php $first = $group->first(); @endphp
                <x-ticket 
                    :ticket="$first" 
                    :tab="$tab" 
                    :count="$group->count()" 
                />
            @empty
                <p class="text-gray-500 col-span-full py-10 text-center">チケットはありません。</p>
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