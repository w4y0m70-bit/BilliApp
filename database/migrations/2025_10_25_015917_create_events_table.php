<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100); // イベントタイトル
            $table->text('description')->nullable(); // イベント説明
            $table->dateTime('event_date'); // イベント開催日時
            $table->dateTime('entry_deadline'); // エントリー締切日時
            $table->dateTime('published_at')->nullable(); // 公開日時
            $table->integer('max_participants')->unsigned(); // 最大参加総人数（チームあたり人数✕最大参加枠数）
            $table->integer('max_entries')->default(1); // 最大参加枠数
            $table->integer('max_team_size')->default(1); // チームあたり人数
            $table->boolean('allow_waitlist')->default(true); // キャンセル待ちを許可するか
            $table->foreignId('admin_id')->constrained()->onDelete('cascade'); // 管理者ユーザーID
            $table->unsignedBigInteger('ticket_id')->nullable(); // 使用するチケットID
            $table->string('instruction_label')->nullable(); // 案内ラベル
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['ticket_id']);
            $table->dropColumn('ticket_id');
        });
    }
};
