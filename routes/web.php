<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminEventController;
use App\Http\Controllers\Admin\AdminTicketController;
use App\Http\Controllers\Admin\AdminParticipantController;
use App\Http\Controllers\Admin\AdminAccountController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\Auth\AdminRegisterController;
use App\Http\Controllers\User\UserEventController;
use App\Http\Controllers\User\UserEntryController;
use App\Http\Controllers\User\UserProfileController;
use App\Http\Controllers\User\UserLoginController;
use App\Http\Controllers\User\Auth\UserRegisterController;
// use App\Http\Controllers\EventParticipantController;

use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;

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
    // Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'login'])->name('login.post');

        Route::get('/register', [AdminRegisterController::class, 'showRegistrationForm'])->name('register');
        Route::post('/register', [AdminRegisterController::class, 'register'])->name('register.post');
    // });
    // ===== ログイン必須エリア =====
    Route::middleware(['auth:admin', 'session.lifetime:20'])->group(function () {

        // ログアウト
        Route::post('logout', [AdminLoginController::class, 'logout'])->name('logout');

        // ホーム
        Route::redirect('/', '/admin/events')->name('home');

        // イベント管理
        Route::resource('events', AdminEventController::class);

        // 参加者管理
        Route::prefix('events/{event}/participants')->name('events.participants.')->group(function () {
            Route::get('/', [AdminParticipantController::class, 'index'])->name('index');
            Route::get('/create', [AdminParticipantController::class, 'create'])->name('create');
            Route::post('/', [AdminParticipantController::class, 'store'])->name('store');
            Route::get('/json', [AdminParticipantController::class, 'json'])->name('json');
            Route::patch('/{entry}/cancel', [AdminParticipantController::class, 'cancel'])->name('cancel');
        });

        // イベント参加者JSON取得
        Route::prefix('admin/events/{event}')->group(function() {
            Route::get('participants/json', [AdminParticipantController::class, 'json']);
            Route::post('participants', [AdminParticipantController::class, 'store']);
        });

        //イベントコピー
        Route::get('events/{event}/replicate', [AdminEventController::class, 'replicate'])
         ->name('events.replicate');

        // チケット管理
        Route::resource('tickets', AdminTicketController::class);

        // アカウント情報
        Route::get('/account', [AdminAccountController::class, 'show'])->name('account');
        Route::get('/account/edit', [AdminAccountController::class, 'edit'])->name('account.edit');
        Route::patch('/account/update', [AdminAccountController::class, 'update'])->name('account.update');
    });

});

/*
|--------------------------------------------------------------------------
| 🧍‍♂️ 一般プレイヤー側ルート
|--------------------------------------------------------------------------
*/
    Route::prefix('user')->name('user.')->group(function () {

        // --- 認証不要ルート ---
        // Route::middleware('guest:web')->group(function () {
            Route::get('/login', [UserLoginController::class, 'showLoginForm'])->name('login');
            Route::post('/login', [UserLoginController::class, 'login'])->name('login.post');

            Route::get('/register', [UserRegisterController::class, 'showRegistrationForm'])->name('register');
            Route::post('/register', [UserRegisterController::class, 'register'])->name('register.post');
        // });

        // --- 認証必須ルート ---
        Route::middleware(['auth:web', 'session.lifetime:60'])->group(function () {

            // ログアウト
            Route::post('/logout', [UserLoginController::class, 'logout'])->name('logout');

            // イベント一覧・詳細
            Route::get('events', [UserEventController::class, 'index'])->name('events.index');
            Route::get('events/{event}', [UserEventController::class, 'show'])->name('events.show');

            // エントリー処理
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
