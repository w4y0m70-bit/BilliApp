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
        Schema::create('campaign_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('plan_id')->constrained(); // どのランクのチケットがもらえるか
            $table->integer('issue_count')->default(1); // 1コードで何枚チケットを発行するか
            $table->integer('usage_limit')->default(1);  // 先着何名か
            $table->integer('used_count')->default(0);   // 現在何人が使ったか
            $table->timestamp('valid_until')->nullable(); // コード自体の有効期限
            $table->integer('expiry_days')->default(40); // チケットの有効期限（日数）
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_codes');
    }
};
