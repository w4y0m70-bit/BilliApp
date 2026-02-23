<div id="emailChangeModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" onclick="closeEmailModal()"></div>
        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full z-50">
            <div class="bg-white p-6">
                <h3 class="text-lg font-bold mb-4">メールアドレスの変更</h3>
                <p class="text-sm text-gray-600 mb-4">新しいメールアドレスを入力してください。確認メールを送信します。</p>
                <input type="email" id="new_email_input" 
                    class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-user/50 focus:outline-none"
                    placeholder="new-email@example.com">
                <div id="modal_error" class="text-red-500 text-xs mt-2 hidden"></div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                <button type="button" onclick="submitEmailChange()" id="submitBtn"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-user text-base font-medium text-white hover:opacity-90 sm:w-auto sm:text-sm">
                    送信する
                </button>
                <button type="button" onclick="closeEmailModal()"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                    キャンセル
                </button>
            </div>
        </div>
    </div>
</div>