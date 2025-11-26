<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_entries', function (Blueprint $table) {
            $table->id();

            // プレイヤーとイベントの紐づけ（ゲストは user_id null）
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');

            // ゲスト用の名前・性別・クラス
            $table->string('name')->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('class', 20)->nullable();

            // エントリーステータス
            $table->enum('status', ['entry', 'waitlist', 'cancelled'])->default('entry');

            // キャンセル待ち有効期限
            $table->dateTime('waitlist_until')->nullable();

            $table->timestamps();

            // 同じイベントに同じプレイヤーは重複させない
            $table->unique(['event_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_entries');
    }
};
