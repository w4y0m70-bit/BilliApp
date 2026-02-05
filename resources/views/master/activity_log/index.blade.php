<x-master-layout>
    <div class="mb-3">
        <a href="{{ route('master.activity_logs.index') }}" class="btn btn-outline-secondary btn-sm">All</a>
        @foreach($modelTypes as $type)
            @php
                $displayName = class_basename($type); 
                $label = $labels[$displayName] ?? $displayName;
            @endphp

            <a href="{{ route('master.activity_logs.index', ['type' => $type]) }}" 
            class="btn {{ request('type') == $type ? 'btn-primary' : 'btn-outline-primary' }} btn-sm">
            / {{ $label }}
            </a>
        @endforeach
        @if(request()->filled('causer_id') || request()->filled('type'))
            <a href="{{ route('master.activity_logs.index') }}" 
            class="ml-4 text-xs text-red-500 hover:text-red-700 underline">
                絞り込みを解除する
            </a>
        @endif
    </div>
<div class="container">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">操作ログ一覧</h2>
    </x-slot>
    <div style="overflow-x: auto;">
    <table class="table">
        <thead>
            <tr>
                <th>日時</th>
                <th>実行者</th>
                <th>内容</th>
                <th>対象データ</th>
                <th>変更詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr class="border-b-2 border-gray-200 hover:bg-gray-50 transition-colors">
                <td>{{ $log->created_at->format('Y/m/d H:i') }}</td>
                <td class="p-3">
                    @if($log->causer)
                        <a href="{{ route('master.activity_logs.index', ['causer_id' => $log->causer_id]) }}" 
                        class="text-blue-600 hover:underline font-medium">
                            {{ $log->causer->name }}
                        </a>
                    @else
                        <span class="text-gray-400 italic">システム</span>
                    @endif
                </td>
                <td>{{ $log->description }}</td>
                <td>{{ $log->subject_type }} (ID: {{ $log->subject_id }})</td>
                <td>
                    @if(isset($log->properties['old']))
                        <span class="text-red-600 font-bold text-xs">(pre)</span>
                        @foreach($log->properties['old'] as $key => $value)
                            <div>
                                <span class="text-gray-500">{{ $key }}:</span> 
                                {{-- 日付っぽい項目名なら変換して表示 --}}
                                @if(str_contains($key, '_at') && $value)
                                    {{ \Carbon\Carbon::parse($value)->timezone('Asia/Tokyo')->format('Y/m/d H:i') }}
                                @else
                                    {{ $value }}
                                @endif
                            </div>
                        @endforeach
                        <hr class="my-2 border-gray-300">
                    @endif

                    @if(isset($log->properties['attributes']))
                        <span class="text-green-600 font-bold text-xs">(post)</span>
                        @foreach($log->properties['attributes'] as $key => $value)
                            <div>
                                <span class="text-gray-500">{{ $key }}:</span>
                                {{-- 同じく日付なら変換 --}}
                                @if(str_contains($key, '_at') && $value)
                                    {{ \Carbon\Carbon::parse($value)->timezone('Asia/Tokyo')->format('Y/m/d H:i') }}
                                @else
                                    {{ $value }}
                                @endif
                            </div>
                        @endforeach
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>

    {{-- ページネーションのリンク --}}
    {{ $logs->links() }}
</div>
</x-master-layout>