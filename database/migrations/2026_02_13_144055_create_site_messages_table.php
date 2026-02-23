<!-- トップページに表示するメッセージテーブル -->
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('site_messages', function (Blueprint $table) {
            $table->id();
            $table->text('content')->nullable(); // メッセージ本文
            $table->boolean('is_active')->default(true); // 表示・非表示の切り替え
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('site_messages');
    }
};
