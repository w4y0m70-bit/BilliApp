<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // グループ名（例：スクール生）
            $table->text('description')->nullable(); // 説明文
            $table->integer('rank')->default(1);
            $table->string('rank_name')->default('一般');
            $table->foreignId('owner_id')->constrained('admins')->onDelete('cascade'); // 作成した主催者
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
