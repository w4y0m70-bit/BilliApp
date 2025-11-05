<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Entry;
use App\Models\User;
use Carbon\Carbon;

class UserProfileController extends Controller
{
    public function show()
    {
        // 仮ユーザー取得（ログイン導入前）
        $user = User::first();

        // 前回ログインから1年以上経過していたら削除
        if ($user->last_login_at && Carbon::parse($user->last_login_at)->lt(now()->subYear())) {
            $user->delete();
            return redirect('/')->with('message', '1年以上ログインがなかったため、アカウントを削除しました。');
        }

        $entries = Entry::where('user_id', $user->id)->where('status', 'entry')->with('event')->get();
        $waitlist = Entry::where('user_id', $user->id)->where('status', 'waitlist')->with('event')->get();

        return view('user.profile.show', compact('user', 'entries', 'waitlist'));
    }
}
