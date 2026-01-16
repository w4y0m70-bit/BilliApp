<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Master\MasterDashboardController;
use App\Http\Controllers\Master\AdminManagementController;
use App\Http\Controllers\Master\TicketIssueController;

// 'web' ミドルウェアは bootstrap/app.php でかけている場合は不要ですが、念のため含めておくと安全です
Route::middleware(['web', 'auth:admin', 'can:master-only'])->group(function () {

    // マスター専用ダッシュボード
    Route::get('/master/dashboard', [MasterDashboardController::class, 'index'])->name('master.dashboard');

    // 管理者一覧・詳細・編集・削除
    Route::resource('/master/admins', AdminManagementController::class)->names('master.admins');

    // チケットコード発行関係
    Route::get('/master/tickets', [TicketIssueController::class, 'index'])->name('master.tickets.index');
    Route::post('/master/tickets/generate', [TicketIssueController::class, 'store'])->name('master.tickets.store');
    Route::delete('master/tickets/{id}', [App\Http\Controllers\Master\TicketIssueController::class, 'destroy'])
    ->name('master.tickets.destroy');
    
});