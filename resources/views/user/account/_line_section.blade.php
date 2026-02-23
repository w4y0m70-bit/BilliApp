<div class="bg-green-50 p-4 rounded-lg border border-green-200 mt-6">
    <label class="block text-sm font-bold mb-3 text-gray-700 flex items-center">
        <img src="{{ asset('images/LINE_Brand_icon.png') }}" class="w-4 h-4 mr-1">
        LINE連携設定
    </label>

    @if($user->socialAccounts->where('provider', 'line')->isNotEmpty())
        <div class="bg-white p-3 rounded border border-green-200">
            <div class="flex items-center justify-between">
                <span class="text-sm text-green-700 font-medium flex items-center">
                    <span class="material-symbols-outlined mr-1 text-sm">check_circle</span>
                    LINEと連携しています
                </span>

                @if(!empty($user->email) && !empty($user->password))
                    <button type="button" 
                        onclick="if(confirm('LINE連携を解除しますか？')) document.getElementById('line-disconnect-form').submit();"
                        class="text-xs text-red-500 hover:underline">
                        連携を解除する
                    </button>
                @endif
            </div>
        </div>
    @else
        <div class="bg-white p-3 rounded border border-gray-200">
            <p class="text-xs text-gray-500 mb-3">LINEと連携すると、ログインが簡単になり、通知をLINEで受け取れるようになります。</p>
            <x-user.line-auth-button type="link" class="!w-auto !py-1.5 !text-xs" />
        </div>
    @endif
</div>