<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\User\{
    UserLoginController,
    UserEventController,
    UserEntryController,
    UserProfileController,
    UserForgotPasswordController,
    UserResetPasswordController,
    UserGroupController,
    UserSearchController,
    Auth\UserRegisterController,
    Auth\UserLineAuthController
};
use App\Http\Controllers\EventParticipantController;

// LINEログイン・登録（未ログイン時に使用）
// ボタンのリンク先: route('line.login')
Route::get('/login/line', [UserLineAuthController::class, 'redirectToProvider'])->name('line.login');

// LINEからのコールバック（共通）
// LINE Developers側にはこのURLを登録: https://ドメイン/user/login/line/callback
Route::get('/login/line/callback', [UserLineAuthController::class, 'handleProviderCallback'])->name('line.callback');

Route::get('/login', [UserLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UserLoginController::class, 'login'])->name('login.post');

// 2. プレイヤー新規登録：ステップ1（メールアドレス入力画面）
Route::get('/register/email', [UserRegisterController::class, 'showEmailForm'])->name('register.email');
Route::post('/register/email', [UserRegisterController::class, 'sendVerificationEmail'])->name('register.email.post');

// 3. プレイヤー新規登録：ステップ2（メールのリンクをクリックして表示される本登録画面）
Route::get('/register/form/{email}', [UserRegisterController::class, 'showRegistrationForm'])->name('register');

// 4. プレイヤー新規登録：ステップ3（保存処理）
Route::post('/register', [UserRegisterController::class, 'register'])->name('register.post');

// パスワードリセット（ユーザー）
Route::get('forgot-password', [UserForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
// メール送信
Route::post('forgot-password', [UserForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
// メール内のリンクの着地先
Route::get('account/verify-email-change/{token}', [UserProfileController::class, 'verifyEmailChange'])
    ->name('email.verify');
// メール認証リンクをクリックしたときの処理
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('account.show')->with('success', 'メールアドレスの認証が完了しました！');
})->middleware(['auth', 'signed'])
->name('verification.verify');

// 再設定画面
Route::get('reset-password/{token}', [UserResetPasswordController::class, 'showResetForm'])
    ->name('password.reset');
// 再設定処理
Route::post('reset-password', [UserResetPasswordController::class, 'reset'])
        ->name('password.update');

// --- 認証必須ルート ---
Route::middleware(['auth:web', 'session.lifetime:60'])->group(function () {

    // ログアウト
    Route::post('/logout', [UserLoginController::class, 'logout'])->name('logout');

    // イベント一覧・詳細
    Route::get('events', [UserEventController::class, 'index'])->name('events.index');
    Route::get('events/{event}', [UserEventController::class, 'show'])->name('events.show');

    // エントリー処理
    Route::get('events/{event}/create', [UserEntryController::class, 'create'])->name('entries.create');
    Route::post('events/{event}/entry', [UserEntryController::class, 'entry'])->name('entries.entry');
    Route::post('events/{event}/waitlist', [UserEntryController::class, 'waitlist'])->name('entries.waitlist');

    // エントリー一覧（マイページ）
    Route::get('entries', [UserEntryController::class, 'index'])->name('entries.index');

    // エントリー更新
    Route::patch('events/{event}/entries/{entry}', [UserEntryController::class, 'update'])
        ->name('entries.update');

    // エントリープレイヤー一覧
    Route::get('events/{event}/participants', [UserEventController::class, 'participants'])
    ->name('events.participants');

    // 招待処理
    Route::post('events/{event}/entries/{entry}/invite', [UserEntryController::class, 'invite'])
    ->name('entries.invite');
    // 特定の招待中メンバーを取り消す
    Route::delete('events/{event}/entries/{entry}/invite/{member}/cancel', [UserEntryController::class, 'cancelInvitation'])
    ->name('entries.invite.cancel');

    // 勧誘されたユーザーの返答画面
    Route::post('/events/{event}/entries/{entry}/respond', [UserEntryController::class, 'respond'])
        ->name('entries.respond');

    // キャンセル処理
    Route::patch('/events/{event}/cancel/{entryId}', [UserEntryController::class, 'cancel'])
        ->name('entries.cancel');

    // プレイヤーアカウント
    Route::get('account/show', [UserProfileController::class, 'show'])->name('account.show');
    Route::get('account/edit', [UserProfileController::class, 'edit'])->name('account.edit');
    Route::patch('account/update', [UserProfileController::class, 'update'])->name('account.update');

    // パスワード変更
    Route::get('account/password', [UserProfileController::class, 'editPassword'])->name('account.password.edit');
    Route::patch('account/password', [UserProfileController::class, 'updatePassword'])->name('account.password.update');

    // メールアドレス変更リクエスト（モーダルからの送信先）
    Route::post('account/request-email-change', [UserProfileController::class, 'requestEmailChange'])
        ->name('account.email.request');

    // 認証メール再送
    Route::post('/email/verification-notification', [UserProfileController::class, 'sendVerificationEmail'])
        ->middleware(['throttle:6,1'])
        ->name('verification.send');

    // グループ一覧
    Route::get('/groups', [UserGroupController::class, 'index'])->name('groups.index');
    
    // グループへの申請処理
    Route::post('/groups/{group}/apply', [UserGroupController::class, 'apply'])->name('groups.apply');

    // LINE連携解除
    Route::post('/login/line/disconnect', [UserLineAuthController::class, 'disconnect'])->name('line.disconnect');

    // ユーザー検索API (エントリー時のチーム検索用)
    Route::get('/search-users', [UserSearchController::class, 'search'])->name('search-users');

});