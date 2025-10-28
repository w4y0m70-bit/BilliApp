<?php

use App\Http\Controllers\Admin\AdminEventController;
use App\Http\Controllers\Admin\AdminTicketController;
use App\Http\Controllers\Admin\AdminParticipantController;
use App\Http\Controllers\User\EventController;
use App\Http\Controllers\User\EntryController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 🌟 トップページ
Route::get('/', function () {
    return view('welcome'); // TOPページ
})->name('top');

// 🌟 共通認証（将来的に）
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| 🧑‍💼 管理者側
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {

    // 仮ログインページ（今は画面だけ）
    Route::view('/login', 'admin.login')->name('login');
    Route::post('/login', function () {
        // 仮処理：本来は認証
        return redirect()->route('admin.events.index');
    })->name('login.post');

    // ✅ ホーム（イベント一覧をホーム扱い）
    Route::redirect('/', '/admin/events')->name('home');

    // イベント管理（CRUD）
    Route::resource('events', AdminEventController::class);

    // イベント参加者一覧
    Route::get('/events/{event}/participants', [AdminParticipantController::class, 'index'])
        ->name('events.participants.index');

    Route::put('admin/events/{event}', [AdminEventController::class, 'update'])->name('admin.events.update');

    // ゲスト登録フォーム
    Route::get('/events/{event}/participants/create', [AdminParticipantController::class, 'create'])
        ->name('participants.create');

    // ゲスト登録送信
    Route::post('/events/{event}/participants', [AdminParticipantController::class, 'store'])
        ->name('participants.store');

    // 将来的なチケット管理
    Route::resource('tickets', AdminTicketController::class);

    // アカウント情報
    Route::get('/account', [UserController::class, 'account'])->name('account');
});

/*
|--------------------------------------------------------------------------
| 🧍‍♂️ 一般ユーザー側
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:player'])->group(function () {
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');

    // イベント一覧・詳細・エントリー
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::post('/events/{event}/entry', [EntryController::class, 'store'])->name('entries.store');

    // 自分のエントリー一覧
    Route::get('/entries', [EntryController::class, 'index'])->name('entries.index');

    // アカウント情報
    Route::get('/account', [UserController::class, 'account'])->name('account');
});

/*
|--------------------------------------------------------------------------
| 🎯 将来的なスコアボード
|--------------------------------------------------------------------------
*/
Route::view('/scoreboard', 'scoreboard.index')->name('scoreboard');
