<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Master\MasterDashboardController;
use App\Http\Controllers\Master\AdminManagementController;
use App\Http\Controllers\Master\TicketIssueController;
use App\Http\Controllers\Master\PlanController;
use App\Http\Controllers\Master\UserManagementController;
use App\Http\Controllers\Master\EventManagementController;
use App\Http\Controllers\Master\ActivityLogController;
use App\Http\Controllers\Master\ProfileController;
use App\Http\Controllers\Admin\SiteMessageController;
use App\Http\Controllers\Master\MasterLoginController;

// ログインしていない時だけアクセス可能
Route::middleware('guest:admin')->group(function () {
    Route::get('master/login', [MasterLoginController::class, 'showLoginForm'])->name('master.login');
    Route::post('master/login', [MasterLoginController::class, 'login'])->name('master.login.post');
});

Route::middleware(['web', 'auth:admin', 'can:master-only'])
    ->prefix('master') // URLの先頭に /master/ を付与
    ->as('master.')    // ルート名の先頭に master. を付与
    ->group(function () {

        // ダッシュボード
        Route::get('/dashboard', [MasterDashboardController::class, 'index'])->name('dashboard');

        // 管理者管理 (index, create, store, show, edit, update, destroy)
        Route::resource('admins', AdminManagementController::class);

        // プラン設定管理
        Route::resource('plans', PlanController::class);

        // チケットコード発行関係
        // ※URLの一貫性を保つため、storeのURLを /generate から標準の /tickets に変更案
        Route::get('/tickets', [TicketIssueController::class, 'index'])->name('tickets.index');
        Route::post('/tickets', [TicketIssueController::class, 'store'])->name('tickets.store');
        Route::delete('/tickets/{id}', [TicketIssueController::class, 'destroy'])->name('tickets.destroy');
        
        // 登録ユーザー管理（ユーザーは自分で登録するため、create/storeは不要な場合が多い）
        Route::resource('users', UserManagementController::class)->only(['index', 'show', 'destroy']);

        // イベント管理
        Route::resource('events', EventManagementController::class)->only(['index', 'show', 'destroy']);

        // アカウント
        Route::get('/password', [ProfileController::class, 'editPassword'])->name('password.edit');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');

        // お知らせ設定
        Route::get('/site-message', [SiteMessageController::class, 'edit'])->name('site-message.edit');
        Route::put('/site-message', [SiteMessageController::class, 'update'])->name('site-message.update');

        // アクティビティログ
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity_logs.index');

        // ログアウト
        Route::post('logout', [MasterLoginController::class, 'logout'])->name('logout');
        });