<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminEventController;
use App\Http\Controllers\Admin\AdminTicketController;
use App\Http\Controllers\Admin\AdminParticipantController;
use App\Http\Controllers\User\UserEventController;
use App\Http\Controllers\User\UserEntryController;
use App\Http\Controllers\User\UserProfileController;
use App\Http\Controllers\User\Auth\LoginController;

require __DIR__.'/auth.php';

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
    // 仮ログイン（認証未実装）
    Route::view('/login', 'admin.login')->name('login');
    Route::post('/login', function () {
        return redirect()->route('admin.events.index');
    })->name('login.post');

    // ホーム（＝イベント一覧）
    Route::redirect('/', '/admin/events')->name('home');

    // イベント管理（CRUD）
    Route::resource('events', AdminEventController::class);

    // イベント参加者管理
    Route::get('events/{event}/participants', [AdminParticipantController::class, 'index'])
        ->name('events.participants.index');
    Route::get('events/{event}/participants/create', [AdminParticipantController::class, 'create'])
        ->name('participants.create');
    Route::post('events/{event}/participants', [AdminParticipantController::class, 'store'])
        ->name('participants.store');

    // チケット管理（将来用）
    Route::resource('tickets', AdminTicketController::class);

    // アカウント情報（※後で実装予定）
    Route::view('/account', 'admin.account')->name('account');

    // イベントをコピーして新規作成画面に遷移
    Route::get('events/{event}/replicate', [AdminEventController::class, 'replicate'])
    ->name('events.replicate');


});

/*
|--------------------------------------------------------------------------
| 🧍‍♂️ 一般ユーザー側ルート
|--------------------------------------------------------------------------
*/
Route::prefix('user')->name('user.')->group(function () {
    // 仮ログイン
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.post');

    // イベント一覧・詳細
    Route::get('events', [UserEventController::class, 'index'])->name('events.index');
    Route::get('events/{event}', [UserEventController::class, 'show'])->name('events.show');

    // ✅ エントリー処理関連
    Route::post('events/{event}/entry', [UserEntryController::class, 'entry'])->name('entries.entry');
    Route::post('entries/{id}/cancel', [UserEntryController::class, 'cancel'])->name('entries.cancel');
    Route::post('events/{event}/waitlist', [UserEntryController::class, 'waitlist'])->name('entries.waitlist');

    // エントリー一覧（マイページ）
    Route::get('entries', [UserEntryController::class, 'index'])->name('entries.index');

    // プロフィール
    Route::get('profile', [UserProfileController::class, 'show'])->name('profile.show');
});

/*
|--------------------------------------------------------------------------
| 🎯 スコアボード（将来拡張）
|--------------------------------------------------------------------------
*/
Route::view('/scoreboard', 'scoreboard.index')->name('scoreboard');
