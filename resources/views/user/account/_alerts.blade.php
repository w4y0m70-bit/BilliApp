{{-- 警告メッセージ --}}
@if (session('warning'))
    <div class="mb-4 flex items-center p-4 text-amber-800 border-t-4 border-amber-300 bg-amber-50" role="alert">
        <span class="material-symbols-outlined mr-2">warning</span>
        <div class="text-sm font-medium">{{ session('warning') }}</div>
    </div>
@endif

{{-- エラーメッセージ --}}
@if (session('error'))
    <div class="mb-4 flex items-center p-4 text-red-800 border-t-4 border-red-300 bg-red-50" role="alert">
        <span class="material-symbols-outlined mr-2">error</span>
        <div class="text-sm font-medium">{{ session('error') }}</div>
    </div>
@endif

{{-- バリデーションエラーの一括表示 (任意) --}}
@if ($errors->any())
    <div class="mb-4 bg-red-50 text-red-700 p-3 rounded-lg text-sm">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif