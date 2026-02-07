@extends('admin.layouts.app')
@section('title', 'バッジメンバー・申請管理')

@section('content')
<div class="px-4 py-2">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">バッジメンバー・申請管理</h2>
        <a href="{{ route('admin.badges.create') }}" class="bg-admin text-white px-4 py-2 rounded-lg hover:bg-admin-dark transition font-bold text-sm">
            + 新規バッジ作成
        </a>
    </div>

    @forelse($badges as $badge)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-8 overflow-hidden">
            {{-- ヘッダー部分 --}}
        <div class="bg-gray-50 px-6 py-3 border-b border-gray-100 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <h3 class="font-bold text-gray-700">{{ $badge->name }}</h3>
                <span class="text-sm text-gray-500">（{{ $badge->description ?? '説明はありません。' }}）</span>
            </div>

            {{-- ★追加：アクションボタン（編集・削除） --}}
            <div class="flex items-center space-x-2">
                {{-- 編集ボタン --}}
                <a href="{{ route('admin.badges.edit', $badge->id) }}" class="text-gray-400 hover:text-blue-600 transition p-1" title="バッジを編集">
                    <span class="material-symbols-outlined text-[20px]">edit</span>
                </a>

                {{-- 削除ボタン --}}
                <form action="{{ route('admin.badges.destroy', $badge->id) }}" method="POST" 
                    onsubmit="return confirm('バッジ「{{ $badge->name }}」を完全に削除しますか？\n※このバッジが必要なイベントの制限も解除されます。')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-gray-400 hover:text-red-600 transition p-1" title="バッジを削除">
                        <span class="material-symbols-outlined text-[20px]">delete</span>
                    </button>
                </form>
            </div>
        </div>
            <div class="p-6">
                {{-- 1. 未承認の申請セクション --}}
                <div class="mb-8">
                    <h4 class="text-sm font-bold text-orange-500 uppercase tracking-wider mb-4 flex items-center">
                        <span class="w-2 h-2 bg-orange-500 rounded-full mr-2"></span>
                        未承認の申請
                    </h4>
                    @php $pendingUsers = $badge->users->where('pivot.status', 'pending'); @endphp
                    
                    @if($pendingUsers->isEmpty())
                        <p class="text-gray-400 text-sm pl-4">現在、新しい申請はありません。</p>
                    @else
                        <div class="divide-y divide-gray-100 border-t border-b">
                            @foreach($pendingUsers as $user)
                                <div class="flex items-center justify-between py-3 px-2">
                                    <div class="flex items-center space-x-3">
                                        <p class="text-gray-800 font-bold">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-400">申請日: {{ $user->pivot->created_at->format('m/d H:i') }}</p>
                                    </div>
                                    <form action="{{ route('admin.badges.approve', [$badge->id, $user->id]) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="bg-admin text-white px-4 py-1 rounded-full hover:bg-admin-dark transition text-sm font-bold shadow-sm">
                                            承認する
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- 2. 承認済みメンバーセクション --}}
                <div>
                    <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4 flex items-center">
                        <span class="w-2 h-2 bg-gray-400 rounded-full mr-2"></span>
                        承認済みメンバー
                    </h4>
                    @php $approvedUsers = $badge->users->where('pivot.status', 'approved'); @endphp

                    @if($approvedUsers->isEmpty())
                        <p class="text-gray-400 text-sm pl-4">承認済みのメンバーはいません。</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($approvedUsers as $user)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100">
                                    <div class="flex items-center overflow-hidden">
                                        <!-- <div class="w-8 h-8 bg-admin text-white rounded-full flex items-center justify-center mr-3 text-xs font-bold flex-shrink-0">
                                            {{ mb_substr($user->name, 0, 1) }}
                                        </div> -->
                                        <div class="overflow-hidden">
                                            <p class="text-gray-800 font-bold text-sm truncate">{{ $user->name }}</p>
                                            <!-- <p class="text-[10px] text-gray-400">承認済み</p> -->
                                        </div>
                                    </div>

                                    {{-- 解除ボタン --}}
                                    <form action="{{ route('admin.badges.remove_member', [$badge->id, $user->id]) }}" method="POST" 
                                        onsubmit="return confirm('本当に「{{ $user->name }}」を解除しますか？（再度参加するには申請が必要になります）')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-red-500 transition p-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white p-12 rounded-xl shadow-sm border border-gray-100 text-center">
            <p class="text-gray-500">作成済みのバッジがありません。</p>
        </div>
    @endforelse
</div>
@endsection