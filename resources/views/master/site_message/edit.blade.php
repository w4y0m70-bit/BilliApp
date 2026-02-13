<x-master-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            お知らせ管理
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- 更新完了メッセージ --}}
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-4 rounded-lg border border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('master.site-message.update') }}" method="POST" class="bg-white shadow-sm sm:rounded-lg p-6 border border-gray-200">
                @csrf
                @method('PUT')

                <div class="mb-6">
                    <label for="content" class="block mb-2 text-sm font-bold text-gray-700">
                        トップ画面メッセージ
                    </label>
                    <textarea 
                        id="content"
                        name="content" 
                        rows="5" 
                        class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                        placeholder="例：◯月◯日はメンテナンスのため利用できません。"
                    >{{ old('content', $message->content) }}</textarea>
                    <p class="mt-2 text-xs text-gray-500">
                        ※ 改行して入力すると、トップ画面では箇条書き（・）で表示されます。
                    </p>
                </div>

                <div class="mb-6">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" {{ $message->is_active ? 'checked' : '' }}>
                        <span class="ml-2 text-sm text-gray-600">トップ画面に表示する</span>
                    </label>
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition">
                        設定を保存
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-master-layout>