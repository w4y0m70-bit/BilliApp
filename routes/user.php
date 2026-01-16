<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\{
    UserLoginController,
    UserEventController,
    UserEntryController,
    UserProfileController,
    Auth\UserRegisterController
};

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