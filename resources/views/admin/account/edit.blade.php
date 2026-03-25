@extends('admin.layouts.app')
@section('title', 'アカウント情報編集')

@section('content')
    {{-- yubinbango.js の読み込み --}}
    <script src="https://yubinbango.github.io/yubinbango/yubinbango.js" charset="UTF-8"></script>

    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden">
            {{-- ヘッダー：詳細画面と共通のデザイン --}}
            <div class="border-b border-gray-100 px-8 py-6 bg-gray-50/50">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <span class="material-symbols-outlined mr-2 text-admin text-3xl">edit_note</span>
                    アカウント情報編集
                </h2>
            </div>

            <form action="{{ route('admin.account.update') }}" method="POST" class="h-adr p-4 space-y-6">
                @csrf
                @method('PATCH')

                {{-- 国名指定（yubinbango用） --}}
                <span class="p-country-name" style="display:none;">Japan</span>

                {{-- 1. 基本情報セクション --}}
                <section class="space-y-6">
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center mb-6">
                        <span class="w-1.5 h-1.5 bg-admin rounded-full mr-2"></span>
                        基本情報
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form.input name="admin_id" label="ログインID" :value="$admin->admin_id" required />
                        <x-form.input name="name" label="店舗名（主催者名）" :value="$admin->name" required />
                    </div>
                    <x-form.input name="manager_name" label="担当者名" :value="$admin->manager_name" />
                </section>

                <hr class="border-gray-100">

                {{-- 2. 所在地セクション（背景色をつけてグループ化） --}}
                <section class="bg-gray-50/80 p-6 rounded-2xl border border-gray-100">
                    <h3
                        class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center mb-6 text-admin">
                        <span class="material-symbols-outlined text-sm mr-1">location_on</span>
                        所在地
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2">
                        {{-- 郵便番号 --}}
                        <x-form.input name="zip_code" label="郵便番号" :value="$admin->zip_code" info="ハイフンなし" class="p-postal-code"
                            placeholder="1234567" />

                        {{-- 都道府県（yubinbango連携） --}}
                        <x-form.input name="prefecture" label="都道府県" :value="$admin->prefecture"
                            class="p-region bg-gray-100 cursor-not-allowed" readonly />

                        {{-- 市区町村 --}}
                        <x-form.input name="city" label="市区町村" :value="$admin->city" class="p-locality" />

                        {{-- 番地・建物名 --}}
                        <x-form.input name="address_line" label="番地・建物名" :value="$admin->address_line"
                            class="p-street-address p-extended-address" />
                    </div>
                </section>

                <hr class="border-gray-100">

                {{-- 3. 連絡先・通知セクション --}}
                <section class="space-y-8">
                    <x-form.input name="phone" label="電話番号" :value="$admin->phone" type="tel"
                        placeholder="09012345678" />

                    <div class="space-y-4">
                        <label
                            class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-4 flex items-center">
                            <span class="w-1.5 h-1.5 bg-admin rounded-full mr-2"></span>
                            通知設定
                        </label>

                        @php
                            $adminNotificationTypes = [
                                'event_full' => 'イベントが満員になった時',
                                'event_deadline' => 'エントリーが締切られた時',
                            ];
                            $notificationVias = ['mail' => 'メール', 'line' => 'LINE'];
                        @endphp

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach ($adminNotificationTypes as $type => $label)
                                <div class="p-4 bg-white border border-gray-100 rounded-xl shadow-sm">
                                    <span class="block font-bold text-sm text-gray-700 mb-3">{{ $label }}</span>
                                    <div class="flex gap-4">
                                        @foreach ($notificationVias as $viaKey => $viaLabel)
                                            @php
                                                $isChecked = $admin->notificationSettings
                                                    ->where('type', $type)
                                                    ->where('via', $viaKey)
                                                    ->where('enabled', true)
                                                    ->isNotEmpty();
                                                $isLineDisabled = $viaKey === 'line' && !$hasLine;
                                            @endphp
                                            <div class="flex items-center">
                                                <x-form.checkbox name="event_full" label="満員通知" type="admin"
                                                    :checked="$isChecked" />
                                            </div>
                                        @endforeach
                                    </div>
                                    @if ($isLineDisabled)
                                        <p class="mt-2 text-[10px] text-red-400 italic">LINE未連携のため設定不可</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                {{-- ボタンエリア --}}
                <div class="flex flex-col sm:flex-row items-center justify-between border-t border-gray-100 pt-10 gap-4">
                    <a href="{{ route('admin.account.show') }}"
                        class="text-sm font-bold text-gray-400 hover:text-gray-600 transition flex items-center order-2 sm:order-1">
                        <span class="material-symbols-outlined text-base mr-1">arrow_back</span>
                        変更せずに戻る
                    </a>
                    <button type="submit"
                        class="w-full sm:w-auto bg-admin hover:bg-admin-dark text-white px-10 py-3 rounded-full font-bold shadow-lg shadow-admin/20 transition-all transform hover:-translate-y-0.5 active:translate-y-0 order-1 sm:order-2 text-center">
                        設定を更新する
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
