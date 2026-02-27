<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AdminLineAuthController extends Controller
{
    // LINEの認証画面へリダイレクト
    public function redirectToProvider()
    {
        // 管理者としてログインしている状態でのみ実行
        return Socialite::driver('line')
            ->with(['redirect_uri' => route('admin.line.callback')]) 
            ->redirect();
    }

    // LINEからのコールバック処理
    public function handleProviderCallback()
    {
        try {
            $lineUser = Socialite::driver('line')
                ->with(['redirect_uri' => route('admin.line.callback')])
                ->user();
        } catch (\Exception $e) {
            return redirect()->route('admin.account.show')->with('error', 'LINE連携に失敗しました。');
        }

        $admin = Auth::guard('admin')->user();

        // LINE IDが既に他の管理者に使われていないかチェック
        // (SocialAccountモデルなどで管理している場合)
        $admin->socialAccounts()->updateOrCreate(
            ['provider' => 'line'],
            ['provider_id' => $lineUser->getId()]
        );

        return redirect()->route('admin.account.show')->with('success', 'LINE連携が完了しました。');
    }

    // 連携解除
    public function disconnect()
    {
        $admin = Auth::guard('admin')->user();
        if ($admin->socialAccounts()) { // リレーションが存在するか確認
            $admin->socialAccounts()->where('provider', 'line')->delete();
        }

        return redirect()->route('admin.account.show')->with('success', 'LINE連携を解除しました。');
    }
}