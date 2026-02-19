@props([
    'participants', 
    'isAdmin' => false, 
    'nameFormat' => 'public'
])

<div class="overflow-x-auto bg-white shadow rounded-xl">
    <table class="min-w-full leading-normal border border-gray-300">
        <thead>
            <tr class="bg-gray-100 border-b text-left text-xs font-semibold text-gray-600 uppercase">
                <th class="px-5 py-3 w-2/12">No.</th>
                <th class="px-5 py-3">氏名 / アカウント名</th>
                <th class="px-5 py-3 w-2/12">クラス</th>
                <th class="px-4 py-3 w-2/12">回答</th>
                @if($isAdmin) <th class="px-5 py-3 w-2/12 text-center">操作</th> @endif
            </tr>
        </thead>
        {{-- 外側の <tbody> を削除し、ここからループを開始 --}}
        @forelse ($participants as $participant)
            {{-- 各参加者セットを <tbody> として扱う。これが Alpine.js のスコープになる --}}
            <tbody x-data="{ openMessage: false }" class="border-b border-gray-200">
                <tr class="{{ $participant->status === 'waitlist' ? 'bg-orange-50' : 'bg-white' }}">
                    <td class="px-5 py-3 text-sm font-bold">
                        <span class="{{ $participant->status === 'waitlist' ? 'text-orange-600' : '' }}">
                            {{ $participant->status === 'entry' ? '' : 'WL-' }}{{ $participant->order }}
                        </span>
                    </td>

                    <td class="px-5 py-3 text-sm font-bold {{ $participant->gender === '女性' ? 'text-pink-700' : 'text-gray-800' }}">
                        {{ $participant->getDisplayNameByFormat($nameFormat) }}
                        @if(!$participant->user_id)
                            <span class="text-[10px] text-gray-500 rounded border px-1 ml-1">ゲスト</span>
                        @endif
                    </td>

                    <td class="px-5 py-3 text-sm">
                        {{ is_object($participant->class) ? $participant->class->shortLabel() : $participant->class }}
                    </td>

                    <td class="px-4 py-4 text-center">
                        @if(!empty($participant->user_answer))
                            <button @click="openMessage = !openMessage" type="button" 
                                class="inline-flex items-center text-indigo-500 hover:text-indigo-700 focus:outline-none"
                                title="メッセージを表示">
                                <span class="material-icons text-lg" x-show="!openMessage">chat</span>
                                <span class="material-icons text-lg" x-show="openMessage" x-cloak>speaker_notes_off</span>
                            </button>
                        @else
                            <span class="text-gray-300">-</span>
                        @endif
                    </td>

                    @if($isAdmin)
                        <td class="px-5 py-3 text-center">
                            <button 
                                type="button"
                                onclick="window.globalCancelEntry({{ $participant->event_id }}, {{ $participant->id }})" 
                                class="text-red-500 hover:text-red-700 font-bold">
                                ✕
                            </button>
                        </td>
                    @endif
                </tr>

                {{-- メッセージ詳細行 --}}
                <tr x-show="openMessage" x-cloak class="bg-indigo-50/50">
                    <td colspan="{{ $isAdmin ? 5 : 4 }}" class="px-8 py-3 italic text-sm text-gray-700">
                        <div class="flex gap-2">
                            <span class="material-icons text-sm mt-0.5 text-indigo-400">subdirectory_arrow_right</span>
                            <div class="whitespace-pre-wrap font-normal text-left">{{ $participant->user_answer }}</div>
                        </div>
                    </td>
                </tr>
            </tbody>
        @empty
            <tbody>
                <tr><td colspan="{{ $isAdmin ? 5 : 4 }}" class="px-5 py-10 text-center text-gray-500">参加者はいません</td></tr>
            </tbody>
        @endforelse
    </table>
</div>