<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained();
            
            // 使用状況
            $table->timestamp('used_at')->nullable(); // NULLなら未使用
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete(); // どのイベントで使ったか
            
            // 期限管理
            $table->timestamp('expired_at'); // 購入日 + 40日
            $table->boolean('is_expiry_notified')->default(false); // 期限切れ通知済みフラグ
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
