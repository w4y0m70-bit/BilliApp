<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('admin_id')->unique()->onDelete('cascade');
            $table->string('name');               // 店舗名
            $table->string('name_kana')->nullable();
            $table->string('manager_name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('zip_code', 7)->nullable();
            $table->string('prefecture')->nullable();
            $table->string('city')->nullable();
            $table->string('address_line')->nullable();
            $table->string('phone')->nullable();
            $table->date('subscription_until')->nullable(); // サブスク期限
            $table->integer('tickets')->default(0);         // 所持チケット
            $table->dateTime('last_login_at')->nullable();  // 最終ログイン
            $table->string('notification_type')->nullable();
            $table->string('role')->default('admin');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
        $table->dropColumn('email_verified_at');
    }
};
