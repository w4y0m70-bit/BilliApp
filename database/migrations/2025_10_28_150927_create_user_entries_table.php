<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_entries', function (Blueprint $table) {
            $table->id();
            //  イベントとの紐づけ
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            // 「誰が申し込んだか」を管理。1人エントリーの場合はその本人が入ります
            $table->foreignId('representative_user_id')->nullable()->constrained('users')->onDelete('cascade');
            // チーム名（チーム名の入力など）
            $table->string('team_name')->nullable();
            // エントリー全体の管理用（以前のものを継続）
            $table->text('user_answer')->nullable(); 
            $table->enum('status', ['entry', 'waitlist', 'cancelled', 'pending'])->default('entry');
            $table->dateTime('applied_at')->nullable();
            $table->integer('order')->nullable();
            // チーム全員が承諾したかどうか
            $table->boolean('is_confirmed')->default(false);
            // 仮押さえの有効期限（24時間後 or 締切時刻）
            $table->dateTime('pending_until')->nullable();
            $table->dateTime('waitlist_until')->nullable();  //  キャンセル待ち期限
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_entries');
    }
};
