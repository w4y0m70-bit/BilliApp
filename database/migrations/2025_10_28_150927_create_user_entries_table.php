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
            // チーム名（ペア名の入力など）
            $table->string('team_name')->nullable();
            // エントリー全体の管理用（以前のものを継続）
            $table->text('user_answer')->nullable(); 
            $table->enum('status', ['entry', 'waitlist', 'cancelled', 'pending'])->default('entry');
            $table->dateTime('waitlist_until')->nullable();  //  キャンセル待ち期限
            $table->timestamps();

            // 同じイベントに同じ人が複数チームの代表者として申し込めないようにする場合（任意）
            $table->unique(['event_id', 'representative_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_entries');
    }
};
