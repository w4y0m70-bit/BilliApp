<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entry_members', function (Blueprint $table) {
            $table->id();
            // どの申し込み（チーム）に属するか
            $table->foreignId('user_entry_id')->constrained('user_entries')->onDelete('cascade');
            
            // ユーザー情報の紐付け
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');

            // 招待状況の管理'pending' (招待中), 'approved' (承諾済み), 'rejected' (拒否)
            $table->string('invite_status', 20)->default('approved');
            // ゲスト用、またはエントリー時のスナップショット
            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name_kana')->nullable();
            $table->string('first_name_kana')->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('class', 20)->nullable();

            $table->timestamps();
            
            // 同じ申し込み内で同じユーザーが重複しないようにする
            $table->unique(['user_entry_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entry_members');
    }
};
