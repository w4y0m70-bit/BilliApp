@extends('admin.layouts.app')
@section('title', 'アカウント情報')

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-8">
        {{-- メッセージ表示（デザイン統一） --}}
        @if (session('success'))
            <div
                class="mb-6 flex items-center bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-sm">
                <span class="material-symbols-outlined mr-2 text-green-500">check_circle</span>
                <span class="text-sm font-bold">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 flex items-center bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg shadow-sm">
                <span class="material-symbols-outlined mr-2 text-red-500">error</span>
                <span class="text-sm font-bold">{{ session('error') }}</span>
            </div>
        @endif

        {{-- メインカード --}}
        <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden">
            {{-- ヘッダー：あえて白背景にしてアイコンと文字で主張 --}}
            <div class="border-b border-gray-100 px-8 py-6 flex justify-between items-center bg-gray-50/50">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <span class="material-symbols-outlined mr-2 text-admin text-3xl">account_circle</span>
                    アカウント情報
                </h2>
                <a href="{{ route('admin.account.edit') }}"
                    class="flex items-center bg-white border border-gray-200 text-gray-700 hover:text-admin hover:border-admin font-bold py-2 px-4 rounded-xl shadow-sm transition-all text-sm">
                    <span class="material-symbols-outlined text-sm mr-1">edit</span>
                    編集
                </a>
            </div>

            <div class="p-8 space-y-10">
                {{-- 1. 基本情報セクション --}}
                <section>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4 flex items-center">
                        <span class="w-1.5 h-1.5 bg-admin rounded-full mr-2"></span>
                        基本情報
                    </h3>
                    <div class="grid grid-cols-1 gap-y-5">
                        <div class="flex flex-col sm:flex-row sm:items-center py-1">
                            <span class="sm:w-1/3 text-gray-500 text-sm font-bold">ログインID</span>
                            <span class="sm:w-2/3 font-mono text-gray-800">{{ $admin->admin_id }}</span>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center py-1">
                            <span class="sm:w-1/3 text-gray-500 text-sm font-bold">店舗名 / 主催者名</span>
                            <span class="sm:w-2/3 font-bold text-lg text-gray-900">{{ $admin->name }}</span>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center py-1">
                            <span class="sm:w-1/3 text-gray-500 text-sm font-bold">担当者名</span>
                            <span class="sm:w-2/3 text-gray-800">{{ $admin->manager_name ?? '未設定' }}</span>
                        </div>
                        <div class="flex flex-col sm:flex-row py-1">
                            <span class="sm:w-1/3 text-gray-500 text-sm font-bold">所在地</span>
                            <div class="sm:w-2/3 text-gray-800">
                                @if ($admin->zip_code)
                                    <p class="text-xs text-gray-400 font-mono mb-1">〒{{ $admin->zip_code }}</p>
                                @endif
                                <p class="leading-relaxed">
                                    {{ $admin->prefecture }}{{ $admin->city }}<br>
                                    {{ $admin->address_line }}
                                </p>
                                @if (!$admin->prefecture && !$admin->address_line)
                                    <span class="text-gray-300 italic text-sm">未登録</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center py-1">
                            <span class="sm:w-1/3 text-gray-500 text-sm font-bold">電話番号</span>
                            <span class="sm:w-2/3 text-gray-800 font-mono">{{ $admin->phone ?? '未設定' }}</span>
                        </div>
                    </div>
                </section>

                <hr class="border-gray-100">

                {{-- 2. 通知設定セクション（新規追加） --}}
                <section>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4 flex items-center">
                        <span class="w-1.5 h-1.5 bg-admin rounded-full mr-2"></span>
                        通知設定
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @php
                            $adminNotificationTypes = [
                                'event_full' => ['label' => 'イベント満員通知', 'icon' => 'group'],
                                'event_deadline' => ['label' => 'エントリー締切通知', 'icon' => 'event_available'],
                            ];
                            $viaLabels = ['mail' => 'メール', 'line' => 'LINE'];
                        @endphp

                        @foreach ($adminNotificationTypes as $type => $info)
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100 shadow-sm">
                                <div class="flex items-center mb-3">
                                    <span
                                        class="material-symbols-outlined text-gray-400 text-sm mr-1.5">{{ $info['icon'] }}</span>
                                    <span class="text-sm font-bold text-gray-700">{{ $info['label'] }}</span>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    @foreach (['mail', 'line'] as $via)
                                        @php
                                            $isEnabled = $admin->notificationSettings
                                                ->where('type', $type)
                                                ->where('via', $via)
                                                ->where('enabled', true)
                                                ->isNotEmpty();
                                        @endphp

                                        @if ($isEnabled)
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-white text-admin border border-admin/20 shadow-sm">
                                                <span class="material-symbols-outlined text-[12px] mr-1">check_circle</span>
                                                {{ $viaLabels[$via] }}
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-gray-100 text-gray-400 border border-gray-200">
                                                <span
                                                    class="material-symbols-outlined text-[12px] mr-1 opacity-50">do_not_disturb_on</span>
                                                {{ $viaLabels[$via] }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <hr class="border-gray-100 my-10">

                {{-- 2. ログイン・セキュリティセクション --}}
                <section>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4 flex items-center">
                        <span class="w-1.5 h-1.5 bg-admin rounded-full mr-2"></span>
                        セキュリティ
                    </h3>
                    <div class="space-y-3">
                        {{-- 各項目を「小さな白いカード」風にして操作感を出す --}}
                        @foreach ([['label' => 'メールアドレス', 'value' => $admin->email, 'action' => 'openEmailModal()', 'btnLabel' => '変更'], ['label' => 'パスワード', 'value' => !empty($admin->password) ? '••••••••••••' : null, 'action' => "location.href='" . route('admin.account.password.edit')
                            . "'", 'btnLabel' => !empty($admin->password) ? '変更' : '設定']] as $item)
                            <div
                                class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100 shadow-sm transition-hover hover:bg-white hover:border-admin/20">
                                <div>
                                    <span
                                        class="block text-[10px] font-black text-gray-400 uppercase">{{ $item['label'] }}</span>
                                    <span
                                        class="text-sm font-bold {{ $item['value'] ? 'text-gray-700' : 'text-red-400 italic' }}">
                                        {{ $item['value'] ?? '未設定' }}
                                    </span>
                                </div>
                                <button onclick="{{ $item['action'] }}"
                                    class="text-xs font-bold text-admin bg-white border border-admin/20 px-4 py-1.5 rounded-lg hover:bg-admin hover:text-white transition shadow-sm">
                                    {{ $item['btnLabel'] }}
                                </button>
                            </div>
                        @endforeach

                        {{-- LINE連携（特殊スタイル） --}}
                        <div
                            class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100 shadow-sm transition-hover hover:bg-white hover:border-green-200">
                            <div>
                                <span class="block text-[10px] font-black text-gray-400 uppercase">LINE連携</span>
                                <div class="flex items-center mt-0.5">
                                    @if ($admin->socialAccounts)
                                        <span class="flex items-center text-green-600 text-xs font-bold">
                                            <span class="w-2 h-2 mr-1.5 bg-green-500 rounded-full animate-pulse"></span>連携済み
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs font-bold italic">未連携</span>
                                    @endif
                                </div>
                            </div>
                            @if ($admin->socialAccounts)
                                <form action="{{ route('admin.line.disconnect') }}" method="POST"
                                    onsubmit="return confirm('LINE連携を解除しますか？');">
                                    @csrf
                                    <button type="submit"
                                        class="text-xs font-bold text-red-500 bg-white border border-red-200 px-4 py-1.5 rounded-lg hover:bg-red-500 hover:text-white transition shadow-sm">解除</button>
                                </form>
                            @else
                                <a href="{{ route('admin.line.login') }}"
                                    class="flex items-center bg-[#06C755] text-white text-[11px] font-bold px-4 py-2 rounded-lg hover:bg-[#05b34c] transition shadow-sm">
                                    <i class="fa-brands fa-line mr-1 text-base"></i> LINE連携
                                </a>
                            @endif
                        </div>
                    </div>
                </section>
            </div>

            {{-- フッター：最終ログイン --}}
            <div class="bg-gray-50 px-8 py-4 border-t border-gray-100">
                <p class="text-[10px] text-gray-400 font-bold flex items-center justify-end uppercase tracking-widest">
                    <span class="material-symbols-outlined text-sm mr-1">history</span>
                    Last Login: {{ $admin->last_login_at ? $admin->last_login_at->format('Y.m.d H:i') : 'No Record' }}
                </p>
            </div>
        </div>
    </div>

    {{-- メール変更モーダル --}}
    <div id="email-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeEmailModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
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
                    <button type="button" onclick="submitEmailChange()" id="email-submit-btn"
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
                    body: JSON.stringify({
                        new_email: email
                    })
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
