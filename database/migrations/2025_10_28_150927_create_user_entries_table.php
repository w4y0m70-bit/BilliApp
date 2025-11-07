<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_entries', function (Blueprint $table) {
            $table->id();

            // 🧍‍♂️ ユーザーとイベントの紐づけ
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');

            // ゲストユーザー名（通常ユーザーは user_id 経由）
            $table->string('name')->nullable();

            // 🏷️ エントリーステータス
            $table->enum('status', ['entry', 'waitlist', 'cancelled'])->default('entry');

            // ⏳ キャンセル待ち有効期限
            $table->dateTime('waitlist_until')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_entries');
    }
};
