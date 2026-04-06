@extends('admin.layouts.app')
@section('title', 'チケット')

@section('content')
    <div class="px-4 py-4"> {{-- 余白を調整 --}}
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                チケット管理
                <x-help help-key="admin.tickets.index" class="ml-2" />
            </h2>
        </div>

        {{-- 1. コード入力セクション --}}
        <div class="bg-white shadow rounded-lg border border-gray-100 p-5 mb-8">
            <div class="flex items-center mb-4">
                <span class="material-symbols-outlined text-admin mr-2">confirmation_number</span>
                <h3 class="text-lg font-bold text-gray-700">キャンペーンコード入力</h3>
                <x-help help-key="admin.tickets.use_code" class="ml-1" />
            </div>

            <form action="{{ route('admin.tickets.use_code') }}" method="POST" class="flex flex-col md:flex-row gap-3">
                @csrf
                <div class="relative flex-grow max-w-md">
                    <input type="text" name="code"
                        class="form-control w-full border-gray-300 rounded-lg focus:ring-admin focus:border-admin pl-4 py-2.5"
                        placeholder="お手持ちのコードを入力" required>
                </div>

                <button type="submit"
                    class="bg-admin text-white px-8 py-2.5 rounded-lg hover:bg-admin-dark transition whitespace-nowrap font-bold shadow-sm flex items-center justify-center">
                    <span class="material-symbols-outlined mr-2 text-base text-white">add_circle</span>
                    チケットを受け取る
                </button>
            </form>
        </div>

        {{-- 2. タブ切り替え（イベント一覧のデザイン構造を継承） --}}
        <div class="max-w-full mx-auto mb-6">
            <div class="flex border-b border-gray-200 space-x-8 overflow-x-auto px-1">

                {{-- 利用可能 --}}
                <a href="{{ route('admin.tickets.index', ['tab' => 'ready']) }}"
                    class="py-4 px-1 border-b-2 font-bold text-sm flex items-center whitespace-nowrap transition-colors {{ $tab === 'ready' ? 'border-admin text-admin' : 'border-transparent text-gray-400 hover:text-gray-700' }}">
                    利用可能
                </a>

                {{-- 使用中 --}}
                <a href="{{ route('admin.tickets.index', ['tab' => 'active']) }}"
                    class="py-4 px-1 border-b-2 font-bold text-sm flex items-center whitespace-nowrap transition-colors {{ $tab === 'active' ? 'border-admin text-admin' : 'border-transparent text-gray-400 hover:text-gray-700' }}">
                    使用中
                </a>

                {{-- 使用済み履歴 --}}
                <a href="{{ route('admin.tickets.index', ['tab' => 'used']) }}"
                    class="py-4 px-1 border-b-2 font-bold text-sm flex items-center whitespace-nowrap transition-colors {{ $tab === 'used' ? 'border-admin text-admin' : 'border-transparent text-gray-400 hover:text-gray-700' }}">
                    使用済み
                </a>
            </div>
        </div>

        {{-- 3. チケット一覧 --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @if ($tab === 'ready')
                {{-- 利用可能タブ：同じ種類のチケットをまとめて表示 --}}
                @forelse($groupedTickets as $group)
                    @php $first = $group->first(); @endphp
                    <x-ticket :ticket="$first" tab="ready" :count="$group->count()" />
                @empty
                    <div
                        class="col-span-full py-12 bg-white rounded-lg border border-dashed border-gray-300 text-center text-gray-500">
                        利用可能なチケットはありません。
                    </div>
                @endforelse
            @else
                {{-- 使用中・使用済みタブ：イベントごとに異なるため、1件ずつ表示 --}}
                @forelse($tickets as $ticket)
                    <x-ticket :ticket="$ticket" :tab="$tab" />
                @empty
                    <div
                        class="col-span-full py-12 bg-white rounded-lg border border-dashed border-gray-300 text-center text-gray-500">
                        {{ $tab === 'active' ? '使用中' : '使用済み' }}のチケットはありません。
                    </div>
                @endforelse
            @endif
        </div>
    </div>

    {{-- アラート表示をトースト通知等に変えるのもありですが、一旦そのまま --}}
    @if (session('success_msg'))
        <script>
            window.onload = () => alert("{{ session('success_msg') }}");
        </script>
    @endif
    @if (session('error_msg'))
        <script>
            window.onload = () => alert("{{ session('error_msg') }}");
        </script>
    @endif
@endsection
