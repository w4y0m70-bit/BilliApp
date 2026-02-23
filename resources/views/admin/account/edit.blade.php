@extends('admin.layouts.app')

@section('title', 'アカウント情報編集')

@section('content')
{{-- yubinbango.js の読み込み --}}
<script src="https://yubinbango.github.io/yubinbango/yubinbango.js" charset="UTF-8"></script>

<div class="max-w-2xl mx-auto">
    <div class="bg-white shadow p-6 rounded-xl">
        <h2 class="text-xl font-bold mb-6 border-b pb-2 flex items-center">
            <span class="material-symbols-outlined mr-2">edit_note</span>
            アカウント情報編集
        </h2>

        <form action="{{ route('admin.account.update') }}" method="POST" class="h-adr">
            @csrf
            @method('PATCH')
            
            {{-- 国名指定（yubinbango用） --}}
            <span class="p-country-name" style="display:none;">Japan</span>

                <x-form.input name="admin_id" label="ログインID" :value="$admin->admin_id" required />

                <x-form.input name="name" label="店舗名（主催者名）" :value="$admin->name" required />

                <x-form.input name="manager_name" label="担当者名" :value="$admin->manager_name" />

            <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <label class="block text-sm font-bold mb-3 text-admin flex items-center">
                    <span class="material-symbols-outlined text-sm mr-1">location_on</span>
                    所在地<span class="text-red-500 ml-1">*</span>
                </label>
                
                <div class="grid grid-cols-1">
                    {{-- 郵便番号 --}}
                    <x-form.input 
                        name="zip_code" 
                        label="郵便番号" 
                        :value="$admin->zip_code" 
                        info="ハイフンなしで入力してください" 
                        class="p-postal-code" 
                        placeholder="1234567" 
                    />

                    {{-- 都道府県（yubinbango用。入力不可だが値は送る必要がある） --}}
                    <x-form.input 
                        name="prefecture" 
                        label="都道府県" 
                        :value="$admin->prefecture" 
                        class="p-region bg-white" 
                        readonly 
                    />

                    {{-- 市区町村 --}}
                    <x-form.input 
                        name="city" 
                        label="市区町村" 
                        :value="$admin->city" 
                        class="p-locality" 
                    />

                    {{-- 番地・建物名 --}}
                    <x-form.input 
                        name="address_line" 
                        label="番地・建物名" 
                        :value="$admin->address_line" 
                        class="p-street-address p-extended-address" 
                    />
                </div>
            </div>

            {{-- 電話番号 --}}
            <x-form.input 
                name="phone" 
                label="電話番号" 
                :value="$admin->phone" 
                type="tel" 
                placeholder="09012345678" 
            />

            <div class="mb-6">
                <label class="block text-sm font-semibold mb-1 text-gray-700">
                    メールアドレス
                    <span class="text-red-500 ml-1">*</span>
                </label>
                <div class="flex gap-2">
                    {{-- name="email" は送信しない（または hidden にする）ことで全体保存時の混乱を防ぐ --}}
                    <input type="email" value="{{ $admin->email }}" readonly
                        class="border rounded w-full p-2 bg-gray-100 border-gray-300 text-gray-500 cursor-not-allowed shadow-sm">
                    
                    <button type="button" onclick="openEmailModal()" 
                            class="shrink-0 px-4 py-2 border border-admin text-admin text-xs font-bold rounded hover:bg-admin hover:text-white transition">
                        変更する
                    </button>
                </div>
            </div>

            {{-- LINE連携セクションの追加（通知設定の上あたり） --}}
            <div class="mb-8 p-4 bg-green-50 rounded-lg border border-green-200">
                <label class="block text-sm font-bold mb-3 text-green-800 flex items-center">
                    <span class="material-symbols-outlined text-sm mr-1">chat</span>
                    LINE連携ステータス
                </label>
                
                <div class="flex items-center justify-between">
                    @if($hasLine)
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-300">
                                <span class="w-2 h-2 mr-1.5 bg-green-500 rounded-full"></span>
                                連携済み
                            </span>
                        </div>
                        <button type="button" onclick="event.preventDefault(); document.getElementById('line-disconnect-form').submit();"
                                class="text-xs text-red-600 hover:underline flex items-center">
                            <!-- <span class="material-symbols-outlined text-sm mr-1">link_off</span> -->
                            連携を解除する
                        </button>
                    @else
                        <div class="flex items-center text-gray-500 text-xs">
                            <span class="material-symbols-outlined text-sm mr-1">info</span>
                            未連携（通知をLINEで受け取るには連携が必要です）
                        </div>
                        <a href="{{ route('admin.line.login') }}" class="bg-[#06C755] hover:bg-[#05b34c] text-white px-4 py-1.5 rounded text-xs font-bold flex items-center shadow-sm">
                            LINEと連携する
                        </a>
                    @endif
                </div>
            </div>

            <div class="mb-8">
                <label class="block font-bold mb-4 border-b pb-2 text-gray-700 text-sm">通知設定</label>
                
                @php
                    $adminNotificationTypes = ['event_full' => 'イベントが満員時に通知'];
                    $notificationVias = ['mail' => 'メール', 'line' => 'LINE'];
                @endphp

                @foreach($adminNotificationTypes as $type => $label)
                    <div class="mb-4">
                        <span class="block font-medium mb-2 text-sm text-gray-600">{{ $label }}</span>
                        <div class="flex gap-6">
                            @foreach($notificationVias as $viaKey => $viaLabel)
                                @php
                                    $isChecked = $admin->notificationSettings
                                        ->where('type', $type)
                                        ->where('via', $viaKey)
                                        ->where('enabled', true)
                                        ->isNotEmpty();
                                        
                                    // LINE未連携時の無効化判定
                                    $isLineDisabled = ($viaKey === 'line' && !$hasLine);
                                @endphp

                                <x-form.checkbox 
                                    name="notifications[{{ $type }}][{{ $viaKey }}]" 
                                    :label="$viaLabel" 
                                    :checked="$isChecked" 
                                    :disabled="$isLineDisabled"
                                />
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex items-center border-t pt-6">
                <button type="submit" class="bg-admin hover:bg-admin-dark text-white px-8 py-2 rounded-full font-bold shadow-md transition-all">
                    更新する
                </button>
                <a href="{{ route('admin.account.show') }}" class="ml-6 text-sm text-gray-500 hover:text-gray-700 underline">
                    キャンセル
                </a>
            </div>
        </form>
        {{-- フォームの外側に解除用隠しフォームを配置 --}}
        @if($hasLine)
        <form id="line-disconnect-form" action="{{ route('admin.line.disconnect') }}" method="POST" class="hidden">
            @csrf
        </form>
        @endif
    </div>
</div>

<div id="email-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeEmailModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <span class="material-symbols-outlined mr-2">mail</span>
                    メールアドレスの変更
                </h3>
                <p class="text-sm text-gray-500 mb-4">
                    新しいメールアドレスを入力してください。認証メールが送信されます。
                </p>
                <input type="email" id="new-email-field" 
                       class="w-full border rounded p-2 focus:ring-2 focus:ring-admin focus:outline-none" 
                       placeholder="example@mail.com">
                <div id="email-error" class="hidden mt-2 text-red-600 text-xs"></div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="submitEmailChange()" 
                        id="email-submit-btn"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-admin text-base font-medium text-white hover:bg-admin-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    認証メールを送信
                </button>
                <button type="button" onclick="closeEmailModal()" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    キャンセル
                </button>
            </div>
        </div>
    </div>
</div>

<script>    
    function openEmailModal() {
        document.getElementById('email-modal').classList.remove('hidden');
        document.getElementById('email-error').classList.add('hidden');
    }

    function closeEmailModal() {
        document.getElementById('email-modal').classList.add('hidden');
    }

    async function submitEmailChange() {
        const email = document.getElementById('new-email-field').value;
        const btn = document.getElementById('email-submit-btn');
        const errorDiv = document.getElementById('email-error');

        if (!email) {
            errorDiv.textContent = 'メールアドレスを入力してください。';
            errorDiv.classList.remove('hidden');
            return;
        }

        // 送信中処理
        btn.disabled = true;
        btn.textContent = '送信中...';

        try {
            const response = await fetch("{{ route('admin.account.email.request') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ new_email: email })
            });

            const result = await response.json();

            if (response.ok) {
                alert('認証メールを送信しました。メール内のリンクをクリックして完了してください。');
                closeEmailModal();
            } else {
                errorDiv.textContent = result.errors?.new_email?.[0] || '送信に失敗しました。';
                errorDiv.classList.remove('hidden');
            }
        } catch (e) {
            errorDiv.textContent = '通信エラーが発生しました。';
            errorDiv.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            btn.textContent = '認証メールを送信';
        }
    }
</script>
@endsection