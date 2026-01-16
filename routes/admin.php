<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\{
    Auth\AdminRegisterController,
    AdminResetPasswordController,
    AdminForgotPasswordController,
    AdminLoginController,
    AdminEventController,
    AdminParticipantController,
    AdminAccountController,
    TicketController
};

// プレフィックスや名前空間は app.php で一括設定するので、中身だけでOK
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
        Route::match(['get', 'post'], 'confirm', [AdminEventController::class, 'confirm'])->name('confirm');
        Route::post('store', [AdminEventController::class, 'store'])->name('store');

        Route::get('{event}/replicate', [AdminEventController::class, 'replicate'])->name('replicate');

        Route::resource('/', AdminEventController::class)
            ->names([
                'index' => 'index',
                'edit' => 'edit',
                'update' => 'update',
                'destroy' => 'destroy',
            ])
            ->parameters(['' => 'event'])
            ->except(['create', 'store', 'show']);

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
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::post('/tickets/use-code', [TicketController::class, 'useCode'])->name('tickets.use_code');

    // アカウント情報
    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/', [AdminAccountController::class, 'show'])->name('show');
        Route::get('edit', [AdminAccountController::class, 'edit'])->name('edit');
        Route::patch('update', [AdminAccountController::class, 'update'])->name('update');
    });

});