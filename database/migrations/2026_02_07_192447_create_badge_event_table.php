<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badge_event', function (Blueprint $table) {
            $table->id();
            // event_id（外部キー）
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            // badge_id（外部キー）
            $table->foreignId('badge_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badge_event');
    }
};
