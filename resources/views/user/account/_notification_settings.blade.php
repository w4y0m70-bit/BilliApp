<div class="pt-4 border-t">
    {{-- <label class="block font-bold mb-4 text-gray-700 text-sm">通知設定</label> --}}
    @php
        $notificationTypes = [
            'event_published' => '新規イベント公開',
            'waitlist_updates' => 'キャンセル待ち（繰り上げ・自動終了）',
            'team_invitations' => 'チーム（招待・承諾・拒否・期限切れ）',
        ];
        $notificationVias = ['mail' => 'メール', 'line' => 'LINE'];
    @endphp

    <div class="space-y-6"> {{-- 各通知タイプごとの塊 --}}
        @foreach ($notificationTypes as $type => $typeLabel)
            <div>
                <p class="text-xs font-bold text-gray-500 mb-2">{{ $typeLabel }}</p>
                <div class="flex flex-wrap gap-x-6 gap-y-2"> {{-- 横並び設定 --}}
                    @foreach ($notificationVias as $viaKey => $viaLabel)
                        @php
                            // --- 1. 表示可能・操作可能かどうかの判定 (isDisabled) ---
                            $isDisabled = false;
                            $reason = '';

                            if ($viaKey === 'mail' && !$user->hasVerifiedEmail()) {
                                $isDisabled = true;
                                $reason = '(メール認証後に利用可)';
                            }

                            if ($viaKey === 'line' && $user->socialAccounts->where('provider', 'line')->isEmpty()) {
                                $isDisabled = true;
                                $reason = '(LINE連携後に利用可)';
                            }

                            // --- 2. チェックを入れるかどうか (isEnabled) ---
                            // 既に設定がDBにある場合
                            $settingExists = $user->notificationSettings->isNotEmpty();
                            if ($settingExists) {
                                $isEnabled = $user->notificationSettings
                                    ->where('type', $type)
                                    ->where('via', $viaKey)
                                    ->where('enabled', true)
                                    ->isNotEmpty();
                            } else {
                                // DBに設定がない場合のデフォルト挙動
                                if ($viaKey === 'mail') {
                                    $isEnabled = $user->hasVerifiedEmail();
                                } elseif ($viaKey === 'line') {
                                    $isEnabled = $user->socialAccounts->where('provider', 'line')->isNotEmpty();
                                } else {
                                    $isEnabled = false;
                                }
                            }
                        @endphp

                        <x-form.checkbox :name="'notifications[' . $type . '][' . $viaKey . ']'" :label="$viaLabel" :checked="$isEnabled" :disabled="$isDisabled"
                            :reason="$reason" />
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
