<div class="mb-4">
    <label class="block font-semibold mb-1 text-gray-700">メールアドレス</label>
    <div class="flex items-start gap-2">
        <div class="flex-grow">
            <input type="email" value="{{ old('email', $user->email) }}"
                class="w-full border p-2 rounded block shadow-sm bg-gray-100 cursor-not-allowed"
                readonly placeholder="example@mail.com">
        </div>
        <div>
            <button type="button" onclick="openEmailModal()" class="bg-gray-600 text-white text-xs px-4 py-2.5 rounded shadow-sm hover:bg-gray-700 whitespace-nowrap">
                変更
            </button>
        </div>
    </div>

    @if($user->email)
        <div class="mt-1">
            @if($user->hasVerifiedEmail())
                <span class="text-green-600 text-[10px] flex items-center">
                    <span class="material-symbols-outlined text-xs mr-1">check_circle</span>認証済み。
                </span>
            @else
                <span class="text-amber-600 text-[10px] flex items-center">
                    <span class="material-symbols-outlined text-xs mr-1">pending</span>未認証。
                </span>
            @endif
        </div>
    @endif
</div>