<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->text('description')->nullable();
            $table->dateTime('event_date');
            $table->dateTime('entry_deadline');
            $table->dateTime('published_at')->nullable(); // ← 公開日時追加
            $table->integer('max_participants')->unsigned();
            $table->boolean('allow_waitlist')->default(true);
            $table->integer('entry_count')->default(0);
            $table->integer('waitlist_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
