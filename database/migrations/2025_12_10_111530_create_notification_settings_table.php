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
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained()->onDelete('cascade');;
            $table->string('type'); // イベントタイプ (event_published, waitlist_promoted, waitlist_cancelled, event_full)
            $table->string('via')->default('mail'); // メール、LINE など
            $table->boolean('enabled')->default(true); // ON/OFF
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
