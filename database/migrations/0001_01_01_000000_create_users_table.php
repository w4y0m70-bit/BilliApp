<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // --- 氏名の分割 ---
            $table->string('last_name')->comment('姓');
            $table->string('first_name')->comment('名');
            $table->string('last_name_kana')->comment('セイ');
            $table->string('first_name_kana')->comment('メイ');
            // ------------------

            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('line_id')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->string('role')->default('player');

            $table->string('gender', 10)->nullable();
            $table->date('birthday')->nullable();

            // --- 住所情報の細分化 ---
            $table->string('zip_code', 7)->nullable();
            $table->string('prefecture')->nullable();
            $table->string('city')->nullable();
            $table->string('address_line')->nullable();
            // -----------------------

            $table->string('phone')->nullable();
            $table->string('account_name')->nullable();
            $table->string('class')->nullable();

            $table->dateTime('last_login_at')->nullable();
            $table->boolean('is_guest')->default(false);
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};