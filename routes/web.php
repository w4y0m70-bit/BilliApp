<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\App;
use App\Mail\TestMail;
use App\Http\Controllers\Admin\{
    Auth\AdminRegisterController,
    AdminResetPasswordController,
    AdminForgotPasswordController,
    AdminLoginController,
    AdminEventController,
    AdminParticipantController,
    AdminTicketController,
    AdminAccountController,
};
use App\Http\Controllers\User\{
    UserLoginController,
    UserEventController,
    UserEntryController,
    UserProfileController,
    Auth\UserRegisterController
};
use App\Services\LineService;

// require __DIR__.'/auth.php';
// Route::get('/test-mail', function() {
//     Mail::to('w4y0m70@gmail.com')->send(new TestMail('これはテストメールです'));
//     return 'メール送信しました';
// });
/*
|--------------------------------------------------------------------------
| 🌟 トップページ
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome'); // TOPページ
})->name('top');

/*
|--------------------------------------------------------------------------
| 🧑‍💼 管理者側ルート
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    // ===== 未ログイン時のみアクセス可能 =====
    Route::middleware('guest:admin')->group(function () {
        Route::middleware('guest:admin')->group(function () {
        // 1. ログイン
        Route::get('login', [AdminLoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AdminLoginController::class, 'login'])->name('login.post');

        // 2. 【第一ステップ】メールアドレス入力画面（ログイン画面のリンク先）
        Route::get('register/email', [AdminRegisterController::class, 'showEmailForm'])->name('register.email');
        Route::post('register/email', [AdminRegisterController::class, 'sendVerificationEmail'])->name('register.email.post');

        // 3. 【第二ステップ】本登録フォーム（メールのURLをクリックした時）
        // ここで {email} パラメータが必要になります
        Route::get('register/form/{email}', [AdminRegisterController::class, 'showRegistrationForm'])
            ->name('register');
            // ->middleware('signed'); // 開発段階ではコメントアウト

        // 4. 【最終ステップ】保存処理
        Route::post('register/store', [AdminRegisterController::class, 'register'])->name('register.post');
    });
        // パスワードリセット（管理者）
        Route::get('forgot-password', [AdminForgotPasswordController::class, 'showLinkRequestForm'])
            ->name('password.request');
        // メール送信
        Route::post('forgot-password', [AdminForgotPasswordController::class, 'sendResetLinkEmail'])
            ->name('password.email');
        // 再設定画面
        Route::get('reset-password/{token}', [AdminResetPasswordController::class, 'showResetForm'])
            ->name('password.reset');
        // 再設定処理
        Route::post('reset-password', [AdminResetPasswordController::class, 'reset'])
            ->name('password.update');
    });

    // ===== ログイン必須エリア =====
    Route::middleware(['auth:admin', 'session.lifetime:20'])->group(function () {

        // ログアウト
        Route::post('logout', [AdminLoginController::class, 'logout'])->name('logout');

        // ホームリダイレクト
        Route::redirect('/', '/admin/events')->name('home');

        // イベント管理
        Route::prefix('events')->name('events.')->group(function () {
            Route::get('create', [AdminEventController::class, 'create'])->name('create');
            Route::post('confirm', [AdminEventController::class, 'confirm'])->name('confirm');
            Route::post('store', [AdminEventController::class, 'store'])->name('store');

            Route::get('{event}/replicate', [AdminEventController::class, 'replicate'])->name('replicate');

            Route::resource('/', AdminEventController::class)->parameters(['' => 'event'])->except(['create','store']);

            // 参加者管理
            Route::prefix('{event}/participants')->name('participants.')->group(function () {
                Route::get('/', [AdminParticipantController::class, 'index'])->name('index');
                Route::get('create', [AdminParticipantController::class, 'create'])->name('create');
                Route::post('/', [AdminParticipantController::class, 'store'])->name('store');
                Route::get('json', [AdminParticipantController::class, 'json'])->name('json');
                Route::patch('{entry}/cancel', [AdminParticipantController::class, 'cancel'])->name('cancel');
            });
        });

        // チケット管理
        Route::resource('tickets', AdminTicketController::class);

        // アカウント情報
        Route::prefix('account')->name('account.')->group(function () {
            Route::get('/', [AdminAccountController::class, 'show'])->name('show');
            Route::get('edit', [AdminAccountController::class, 'edit'])->name('edit');
            Route::patch('update', [AdminAccountController::class, 'update'])->name('update');
        });

    });

});

/*
|--------------------------------------------------------------------------
| 🧍‍♂️ 一般プレイヤー側ルート
|--------------------------------------------------------------------------
*/
    Route::prefix('user')->name('user.')->group(function () {

        // --- 認証不要ルート ---
        // 1. プレイヤーログイン
        Route::get('/login', [UserLoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [UserLoginController::class, 'login'])->name('login.post');

        // 2. プレイヤー新規登録：ステップ1（メールアドレス入力画面）
        // ※これが抜けていたので追加しました
        Route::get('/register/email', [UserRegisterController::class, 'showEmailForm'])->name('register.email');
        Route::post('/register/email', [UserRegisterController::class, 'sendVerificationEmail'])->name('register.email.post');

        // 3. プレイヤー新規登録：ステップ2（メールのリンクをクリックして表示される本登録画面）
        // ※{email} を受け取る専用のルート名にします
        Route::get('/register/form/{email}', [UserRegisterController::class, 'showRegistrationForm'])->name('register');
        // ->middleware('signed');

        // 4. プレイヤー新規登録：ステップ3（保存処理）
        // ※名前の重複を避けるため register.post に統一
        Route::post('/register', [UserRegisterController::class, 'register'])->name('register.post');
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

            // キャンセル処理
            Route::patch('/events/{event}/cancel/{entryId}', [UserEntryController::class, 'cancel'])
                ->name('entries.cancel');

            // プレイヤーアカウント
            Route::get('account/show', [UserProfileController::class, 'show'])->name('account.show');
            Route::get('account/edit', [UserProfileController::class, 'edit'])->name('account.edit');
            Route::patch('account/update', [UserProfileController::class, 'update'])->name('account.update');
        });

    });

/*
|--------------------------------------------------------------------------
| 🎯 スコアボード（将来拡張）
|--------------------------------------------------------------------------
*/
Route::view('/scoreboard', 'scoreboard.index')->name('scoreboard');

// LINEメッセージ送信テスト用ルート
Route::get('/line-broadcast-test', function (LineService $lineService) {
    $result = $lineService->broadcast('Laravelからのテスト送信です！成功しました！');
    
    return $result ? 'LINEにメッセージを送りました！確認してください。' : '送信に失敗しました。ログを確認してください。';
});
Route::get('/line-push-test', function (LineService $lineService) {
    $userId = 'U8e87cb76a5ab6380dc076259970644a7';
    
    $result = $lineService->push($userId, 'これはあなただけに向けた個別メッセージです！');
    
    return $result ? '個別メッセージを送信しました！' : '失敗しました。';
});

/*デバッグ用ヘルプ表示ページ*/
Route::get('/_debug/help', function () {
    abort_unless(App::environment('local'), 404);

    $helps = config('help');

    return view('debug.help', compact('helps'));
});