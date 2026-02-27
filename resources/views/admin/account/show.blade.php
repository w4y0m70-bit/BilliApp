@extends('admin.layouts.app')

@section('title', 'アカウント情報')

@section('content')
<div class="max-w-2xl mx-auto">
    @if (session('success'))
        <div class="alert alert-success" style="color: green; background: #e6fffa; padding: 10px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger" style="color: red; background: #fff5f5; padding: 10px; margin-bottom: 20px;">
            {{ session('error') }}
        </div>
    @endif
    <div class="bg-white shadow rounded-xl overflow-hidden">
        <div class="bg-admin px-6 py-4">
            <h2 class="text-xl font-bold text-white flex items-center">
                <span class="material-symbols-outlined mr-2">account_circle</span>
                アカウント情報
            </h2>
        </div>

        <div class="p-6 space-y-6">
            {{-- 基本情報セクション --}}
            <div class="grid grid-cols-1 gap-y-4">
                <div class="flex flex-col sm:flex-row sm:justify-between border-b pb-2">
                    <span class="text-gray-500 text-sm font-semibold">ログインID</span>
                    <span class="font-mono">{{ $admin->admin_id }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b pb-2">
                    <span class="text-gray-500 text-sm font-semibold">店舗名 / 主催者名</span>
                    <span class="font-bold text-lg">{{ $admin->name }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b pb-2">
                    <span class="text-gray-500 text-sm font-semibold">担当者名</span>
                    <span>{{ $admin->manager_name ?? '未設定' }}</span>
                </div>
                
                {{-- 住所表示：細分化したカラムを統合して表示 --}}
                <div class="flex flex-col sm:flex-row sm:justify-between border-b pb-2">
                    <span class="text-gray-500 text-sm font-semibold">所在地</span>
                    <div class="text-right">
                        @if($admin->zip_code)
                            <p class="text-xs text-gray-400">〒{{ $admin->zip_code }}</p>
                        @endif
                        <p>
                            {{ $admin->prefecture }}{{ $admin->city }}<br>
                            {{ $admin->address_line }}
                        </p>
                        @if(!$admin->prefecture && !$admin->address_line)
                            <span class="text-gray-400 italic">未登録</span>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:justify-between border-b pb-2">
                    <span class="text-gray-500 text-sm font-semibold">電話番号</span>
                    <span>{{ $admin->phone ?? '未設定' }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b pb-2">
                    <span class="text-gray-500 text-sm font-semibold">最終ログイン</span>
                    <span class="text-sm text-gray-600">{{ $admin->last_login_at ? $admin->last_login_at->format('Y/m/d H:i') : '記録なし' }}</span>
                </div>
            </div>

            {{-- 通知設定セクション --}}
            <div class="mt-8 bg-gray-50 p-4 rounded-lg">
                <h3 class="text-sm font-bold text-gray-700 mb-3 flex items-center">
                    <span class="material-symbols-outlined text-sm mr-1">notifications</span>
                    通知設定
                </h3>
                <div class="space-y-3">
                    @php
                        $adminNotificationTypes = [
                            'event_full' => 'イベント満員時の通知',
                        ];
                        $viaLabels = ['mail' => 'メール', 'line' => 'LINE'];
                    @endphp
                    
                    @foreach($adminNotificationTypes as $type => $label)
                        <div class="flex justify-between items-center bg-white p-3 rounded border border-gray-200">
                            <span class="text-sm text-gray-600">{{ $label }}</span>
                            <div class="flex gap-2">
                                @php
                                    $activeVias = $admin->notificationSettings
                                        ->where('type', $type)
                                        ->where('enabled', true)
                                        ->map(fn($setting) => $viaLabels[$setting->via] ?? $setting->via)
                                        ->toArray();
                                @endphp

                                @if(count($activeVias) > 0)
                                    @foreach($activeVias as $via)
                                        <span class="bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded-full font-bold">
                                            {{ $via }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-gray-400 text-xs italic">通知OFF</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-center pt-4">
                <a href="{{ route('admin.account.edit') }}" 
                   class="bg-admin hover:bg-admin-dark text-white font-bold py-2 px-8 rounded-full shadow transition-all">
                    情報を編集する
                </a>
            </div>

            <div class="mt-8">
                <h3 class="text-sm font-bold text-gray-700 mb-3 flex items-center">
                    <span class="material-symbols-outlined text-sm mr-1">security</span>
                    ログイン・セキュリティ
                </h3>
                <div class="grid grid-cols-1 gap-3">
                    {{-- メールアドレス --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                        <div>
                            <span class="text-xs text-gray-500 font-bold block">メールアドレス</span>
                            <span class="text-gray-800">{{ $admin->email }}</span>
                        </div>
                        <button type="button" onclick="openEmailModal()" 
                                class="text-admin text-xs font-bold border border-admin px-3 py-1.5 rounded-full hover:bg-admin hover:text-white transition text-center">
                            変更する
                        </button>
                    </div>

                    {{-- パスワード --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                        <div>
                            <span class="text-xs text-gray-500 font-bold block">パスワード</span>
                            @if(!empty($admin->password))
                                <span class="text-gray-400 tracking-tighter">●●●●●●●●●●</span>
                            @else
                                <span class="text-red-400 text-xs font-medium italic">未設定</span>
                            @endif
                        </div>
                        <a href="{{ route('admin.account.password.edit') }}" class="text-admin text-xs font-bold border border-admin px-3 py-1.5 rounded-full hover:bg-admin hover:text-white transition text-center">
                            {{ !empty($admin->password) ? '変更する' : '設定する' }}
                        </a>
                    </div>

                    {{-- LINE連携 --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                        <div>
                            <span class="text-xs text-gray-500 font-bold block">LINE連携</span>
                            @if($admin->socialAccounts)
                                <span class="inline-flex items-center text-green-600 text-xs font-bold">
                                    <span class="w-2 h-2 mr-1 bg-green-500 rounded-full"></span>連携済み
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">未連携</span>
                            @endif
                        </div>
                        @if($admin->socialAccounts)
                            <form action="{{ route('admin.line.disconnect') }}" method="POST" onsubmit="return confirm('LINE連携を解除しますか？');">
        @csrf
        <button type="submit" class="text-red-500 text-xs font-bold border border-red-500 px-3 py-1.5 rounded-full hover:bg-red-500 hover:text-white transition text-center">
            解除する
        </button>
    </form>
                        @else
                            <a href="{{ route('admin.line.login') }}" class="bg-[#06C755] text-white text-xs font-bold px-3 py-1.5 rounded-full hover:bg-[#05b34c] transition text-center">
                                LINE連携
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- メール変更モーダル --}}
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