<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_code_admin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            // ★重要：同じ「コードID」と「管理者ID」の組み合わせを1つに限定する
            $table->unique(['campaign_code_id', 'admin_id']);
        });
    }
};
