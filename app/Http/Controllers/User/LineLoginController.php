<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Str;

class LineLoginController extends Controller
{
    // 1. LINEの認証画面へリダイレクト
    public function redirectToLine()
    {
        $state = Str::random(40);
        session(['line_state' => $state]); // CSRF対策

        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => config('services.line.client_id'),
            'redirect_uri' => config('services.line.redirect_uri'),
            'state' => $state,
            'scope' => 'openid profile',
        ]);

        return redirect('https://access.line.me/oauth2/v2.1/authorize?' . $query);
    }

    // 2. LINEからのコールバック処理
    public function handleLineCallback(Request $request)
    {
        // 1. Stateの検証（CSRF対策）
        if ($request->state !== session('line_state')) {
            return redirect()->route('user.login')->withErrors(['line' => '不正なリクエストです。もう一度やり直してください。']);
        }

        // 2. アクセストークンの取得
        $response = Http::asForm()->post('https://api.line.me/oauth2/v2.1/token', [
            'grant_type'    => 'authorization_code',
            'code'          => $request->code,
            'redirect_uri'  => config('services.line.redirect_uri'),
            'client_id'     => config('services.line.client_id'),
            'client_secret' => config('services.line.client_secret'),
        ]);

        if (!$response->successful()) {
            return redirect()->route('user.login')->withErrors(['line' => 'LINEからのデータ取得に失敗しました。']);
        }

        $lineData = $response->json();
        
        // 3. プロフィール情報の取得
        $profileResponse = Http::withToken($lineData['access_token'])
            ->get('https://api.line.me/v2/profile');
        
        if (!$profileResponse->successful()) {
            return redirect()->route('user.login')->withErrors(['line' => 'LINEプロフィールの取得に失敗しました。']);
        }

        $profile = $profileResponse->json();
        $lineUserId = $profile['userId']; // LINE固有のIDを取得

        // 4. 判定処理
        if (Auth::check()) {
            // 【パターンA】ログイン中：アカウントとLINE IDを紐付ける
            $user = Auth::user();
            $user->line_id = $lineUserId;
            $user->save();

            return redirect()->route('user.account.show')->with('success', 'LINE連携が完了しました！');
        } else {
            // 【パターンB】未ログイン：LINE IDを使ってログインを試みる
            $user = User::where('line_id', $lineUserId)->first();

            if ($user) {
                Auth::login($user);

                // セッションを再生成して古いリダイレクト先をクリアし、強制的に移動
                $request->session()->regenerate(); 
                return redirect()->intended(route('user.events.index')); 
                
                // もし intended でもダメなら、より強力なこちらを使用：
                // return redirect()->to('/user/events');
            } else {
                // 未連携の場合
                return redirect()->route('user.login')->withErrors([
                    'line' => 'このLINEアカウントは連携されていません。'
                ]);
            }
        }
    }

    /**
     * LINE連携を解除する
     */
    public function disconnect(Request $request)
    {
        $user = Auth::user();
        $user->line_id = null; // IDを消去
        $user->save();

        return back()->with('success', 'LINE連携を解除しました。');
    }
}