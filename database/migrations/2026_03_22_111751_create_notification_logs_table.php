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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            // 通知対象（Admin または User）を扱えるようにポリモーフィック関連を使用
            $table->morphs('notifiable'); 
            $table->string('type');            // 'event_full', 'event_deadline' など
            $table->unsignedBigInteger('event_id')->nullable(); // どのイベントに関する通知か
            $table->timestamp('sent_at');
            $table->timestamps();

            // 検索スピードを上げるためのインデックス
            $table->index(['notifiable_type', 'notifiable_id', 'type', 'event_id'], 'notif_log_search_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
