<!-- ユーザーメールアドレス変更用テーブル -->
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
        Schema::create('user_email_resets', function (Blueprint $some) {
            $some->id();
            $some->bigInteger('user_id')->unsigned()->index();
            $some->string('new_email')->nullable()->index();
            $some->string('token')->index();
            $some->timestamp('created_at')->nullable();

            // 外部キー制約（任意ですが推奨。usersテーブルのidに紐付け）
            $some->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_email_resets');
    }
};