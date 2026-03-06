@props([
    'participants', 
    'mode' => 'user', // 'admin' または 'user'
])

@php
    $isAdmin = $mode === 'admin';
@endphp

<div class="bg-white shadow rounded-xl overflow-hidden border border-gray-200">
    {{-- ■ デスクトップ用：テーブル表示 (md以上) --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full leading-normal text-left">
            <thead>
                <tr class="bg-gray-100 border-b text-xs font-semibold text-gray-600 uppercase">
                    <th class="px-4 py-3 w-16 text-center">No.</th>
                    <th class="px-5 py-3">
                        {{ $isAdmin ? '氏名（アカウント名）' : 'アカウント名' }}
                    </th>
                    <th class="px-5 py-3 w-32">クラス</th>
                    @if($isAdmin)
                        <th class="px-4 py-3 w-24 text-center">回答</th>
                        <th class="px-5 py-3 w-16 text-center">操作</th>
                    @endif
                </tr>
            </thead>
            @foreach ($participants as $participant)
                <tbody x-data="{ openMessage: false }" class="border-b-2 border-gray-200">
                    @foreach($participant->members as $index => $member)
                    <tr class="{{ $participant->status === 'waitlist' ? 'bg-orange-50' : 'bg-white' }} {{ $index > 0 ? 'border-t border-gray-100' : '' }}">
                        @if($index === 0)
                            <td rowspan="{{ $participant->members->count() }}" class="px-4 py-3 text-sm font-bold text-center border-r border-gray-100 bg-gray-50/50">
                                <span class="{{ $participant->status === 'waitlist' ? 'text-orange-600' : '' }}">
                                    {{ $participant->status === 'entry' ? '' : 'WL-' }}{{ $participant->order }}
                                </span>
                            </td>
                        @endif
                        <td class="px-5 py-3 text-sm">
                            <div class="flex items-center gap-2">
                                {{-- 招待状態（管理者の時のみ表示して分かりやすくする） --}}
                                @if($isAdmin)
                                    <span class="material-symbols-outlined text-sm {{ $member->invite_status === 'approved' ? 'text-green-500' : 'text-yellow-500 animate-pulse' }}">
                                        {{ $member->invite_status === 'approved' ? 'check_circle' : 'pending' }}
                                    </span>
                                @endif

                                <span class="font-bold {{ $member->gender === '女性' ? 'text-pink-700' : 'text-gray-800' }}">
                                    @if($isAdmin)
                                        {{ $member->full_name }} 
                                        @if($member->user) <span class="text-xs font-normal text-gray-500">({{ $member->user->account_name }})</span> @endif
                                    @else
                                        {{ $member->user->account_name ?? $member->full_name }}
                                    @endif
                                </span>
                                
                                @if($participant->event->max_team_size > 1 && $member->user_id === $participant->representative_user_id)
                                    <span class="text-[9px] bg-user text-white px-1 rounded-full">代表</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3 text-sm">
                            {{ ($member->class instanceof \App\Enums\PlayerClass) ? $member->class->shortLabel() : ($member->class ?? '—') }}
                        </td>
                        @if($index === 0 && $isAdmin)
                            <td rowspan="{{ $participant->members->count() }}" class="px-4 py-3 text-center border-l border-gray-100">
                                @if(!empty($participant->user_answer))
                                    <button @click="openMessage = !openMessage" class="text-indigo-500 hover:text-indigo-700">
                                        <span class="material-symbols-outlined">chat</span>
                                    </button>
                                @else <span class="text-gray-300">-</span> @endif
                            </td>
                            <td rowspan="{{ $participant->members->count() }}" class="px-5 py-3 text-center border-l border-gray-100">
                                <button onclick="window.globalCancelEntry({{ $participant->event_id }}, {{ $participant->id }})" class="text-red-400 hover:text-red-600 transition">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </td>
                        @endif
                    </tr>
                    @endforeach
                    @if($isAdmin)
                        <tr x-show="openMessage" x-cloak class="bg-indigo-50/30">
                            <td colspan="5" class="px-8 py-3 text-sm italic text-gray-700 whitespace-pre-wrap">{{ $participant->user_answer }}</td>
                        </tr>
                    @endif
                </tbody>
            @endforeach
        </table>
    </div>

    {{-- ■ スマホ用：カード形式 (md未満) --}}
    <div class="md:hidden divide-y divide-gray-200">
        @foreach ($participants as $participant)
            <div x-data="{ openMessage: false }" class="p-4 {{ $participant->status === 'waitlist' ? 'bg-orange-50' : 'bg-white' }}">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-xs font-bold px-2 py-1 rounded {{ $participant->status === 'waitlist' ? 'bg-orange-200 text-orange-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ $participant->status === 'entry' ? 'No.' : 'WL-' }}{{ $participant->order }}
                    </span>
                    @if($isAdmin)
                        <div class="flex gap-3">
                            @if(!empty($participant->user_answer))
                                <button @click="openMessage = !openMessage" class="text-indigo-500"><span class="material-symbols-outlined text-xl">chat</span></button>
                            @endif
                            <button onclick="window.globalCancelEntry({{ $participant->event_id }}, {{ $participant->id }})" class="text-red-500">
                                <span class="material-symbols-outlined text-xl">delete</span>
                            </button>
                        </div>
                    @endif
                </div>

                <div class="space-y-2">
                    @foreach($participant->members as $member)
                        <div class="flex justify-between items-center bg-white/50 p-2 rounded border border-gray-100">
                            <div class="flex items-center gap-2">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold {{ $member->gender === '女性' ? 'text-pink-700' : 'text-gray-800' }}">
                                        @if($isAdmin)
                                            {{ $member->full_name }}
                                        @else
                                            {{ $member->user->account_name ?? $member->full_name }}
                                        @endif
                                        @if($participant->event->max_team_size > 1 && $member->user_id === $participant->representative_user_id)
                                            <span class="text-[8px] bg-user text-white px-1 rounded-full align-middle ml-1">代表</span>
                                        @endif
                                    </span>
                                    @if($isAdmin && $member->user)
                                        <span class="text-[10px] text-gray-500">{{ $member->user->account_name }}</span>
                                    @endif
                                </div>
                            </div>
                            <span class="text-xs font-semibold bg-gray-100 px-2 py-1 rounded text-gray-600">
                                {{ ($member->class instanceof \App\Enums\PlayerClass) ? $member->class->shortLabel() : ($member->class ?? 'ー') }}
                            </span>
                        </div>
                    @endforeach
                </div>
                @if($isAdmin)
                    <div x-show="openMessage" x-cloak class="mt-2 p-3 bg-indigo-50 rounded text-xs text-gray-700 italic border border-indigo-100">{{ $participant->user_answer }}</div>
                @endif
            </div>
        @endforeach
    </div>
</div>