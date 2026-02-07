@extends('user.layouts.app')

@section('title', 'コミュニティグループ一覧')

@section('content')
<div class="px-4">
    <h2 class="text-2xl font-bold mb-4">グループ一覧
        <span class="inline-block mb-4">
            <x-help help-key="user.groups.index" />
        </span>
    </h2>

    @if($groups->isEmpty())
        <p class="text-gray-500 py-10 text-center">公開されているグループはありません。</p>
    @else
    <div 
        class="grid gap-4"
        style="grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));"
    >
        @foreach ($groups as $group)
            @php
                // ログインユーザーとこのグループの関係（中間テーブル）を取得
                $userGroup = Auth::user()->groups()->where('group_id', $group->id)->first();
                $status = $userGroup ? $userGroup->pivot->status : null;
            @endphp

            {{-- カード部分 --}}
            <div class="block bg-white shadow rounded-xl p-4 border hover:shadow-lg transition flex flex-col justify-between">
                <div>
                    <p class="text-sm font-bold text-gray-600">
                        ［{{ $group->owner->name ?? '不明' }}］
                    </p>

                    <h3 class="text-2xl font-black mb-1 text-user">
                        {{ $group->name }}
                    </h3>

                    <p class="text-sm text-gray-700 mt-2">
                        {{ $group->description ?? '説明はありません。' }}
                    </p>
                    
                    <!-- <div class="mt-2">
                         <span class="text-xs font-bold px-2 py-1 bg-gray-100 text-gray-500 rounded">
                             ランク: {{ $group->rank_name }}
                         </span>
                    </div> -->
                </div>

                {{-- ボタン・状態表示 --}}
                <div class="mt-4">
                    @if ($status === 'approved')
                        <div class="w-full text-center bg-green-100 text-green-700 font-bold py-2 rounded-lg border border-green-200">
                            参加中
                        </div>
                    @elseif ($status === 'pending')
                        <div class="w-full text-center bg-orange-100 text-orange-700 font-bold py-2 rounded-lg border border-orange-200">
                            承認待ち...
                        </div>
                    @else
                        {{-- 未申請の場合のみ、申請ボタンを表示 --}}
                        <form action="{{ route('user.groups.apply', $group->id) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                class="w-full bg-user text-white font-bold py-2 rounded-lg hover:bg-user-dark transition shadow-md">
                                参加申請する
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    @endif
</div>

{{-- 通知ダイアログ --}}
@if (session('status'))
    <script>
        window.onload = function() {
            alert("{{ session('status') }}");
        };
    </script>
@endif
@if (session('error'))
    <script>
        window.onload = function() {
            alert("{{ session('error') }}");
        };
    </script>
@endif
@endsection