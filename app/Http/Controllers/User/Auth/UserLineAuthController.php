<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSocialAccount;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\DB;

class UserLineAuthController extends Controller
{
    /**
     * LINEの認証画面へリダイレクト
     */
    public function redirectToProvider()
    {
        return Socialite::driver('line')
            ->with(['redirect_uri' => route('user.line.callback')]) 
            ->redirect();
    }

    /**
     * LINEからのコールバック処理
     */
    public function handleProviderCallback()
    {
        try {
            $socialUser = Socialite::driver('line')->user();
        } catch (\Exception $e) {
            return redirect()->route('user.login')->withErrors(['line' => 'LINE認証に失敗しました。']);
        }

        // すでに同じLINE IDで登録があるか探す
        $socialAccount = UserSocialAccount::where('provider', 'line')
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($socialAccount) {
            // 【ログイン】登録済みならそのままログイン
            Auth::login($socialAccount->user);
            return redirect()->route('user.events.index'); // 任意のダッシュボードへ
        }

        if (Auth::check()) {
            // 【連携】ログイン中なら今のユーザーに紐付け
            $result = $this->linkProvider($socialUser, Auth::user());
            if (!$result) {
                return redirect()->route('user.account.edit')->with('error', 'このLINEアカウントは既に他のユーザーに連携されています。');
            }
            return redirect()->route('user.account.show')->with('success', 'LINE連携が完了しました。');
        }

        // 【新規登録】登録がない＆未ログインなら、新規作成
        return DB::transaction(function () use ($socialUser) {
            // 1. ユーザーを作成
            $user = User::create([
                'account_name' => $socialUser->getName(), // ここにLINE名をセット
                'last_name' => null,
                'first_name' => null,
                'last_name_kana' => null,
                'first_name_kana' => null,
                'email' => $socialUser->getEmail() ?: null,
                'password' => null, // パスワードは後で設定
            ]);

            // 2. SNS連携情報を保存
            $user->socialAccounts()->create([
                'provider' => 'line',
                'provider_id' => $socialUser->getId(),
            ]);

            // 3. ログインさせる
            Auth::login($user);

            // 4. 重要：未入力項目があることを伝えて編集画面へ
            return redirect()->route('user.account.edit')
                ->with('info', 'LINE登録ありがとうございます。氏名（漢字・カナ）などの追加情報を入力してください。');
        });
    }

    /**
     * 既存ユーザーへの紐付けロジック
     */
    private function linkProvider($socialUser, $user)
    {
        $exists = UserSocialAccount::where('provider', 'line')
            ->where('provider_id', $socialUser->getId())
            ->exists();

        if ($exists) {
            return false; // 既に使われている
        }

        $user->socialAccounts()->create([
            'provider' => 'line',
            'provider_id' => $socialUser->getId(),
        ]);
        return true;
    }

    /**
     * LINE連携を解除する
     */
    public function disconnect()
    {
        $user = Auth::user();

        // 安全策：メールアドレスまたはパスワードのどちらかが空の場合、解除を拒否する
        // ※「メールアドレスがあり、かつパスワードが設定されている」ことを条件にするのが最も安全です
        if (empty($user->email) || empty($user->password)) {
            return redirect()->route('user.account.edit')
                ->withErrors(['line_disconnect' => 'LINE連携を解除するには、先にメールアドレスとパスワードを設定してください。解除するとログインできなくなります。']);
        }

        // LINEの連携情報を削除
        $user->socialAccounts()->where('provider', 'line')->delete();

        return redirect()->route('user.account.edit')
            ->with('success', 'LINE連携を解除しました。');
    }
}