<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityLogTable extends Migration
{
    public function up()
    {
        Schema::connection(config('activitylog.database_connection'))->create(config('activitylog.table_name'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            // subject
            $table->nullableMorphs('subject', 'subject');
            // event (afterを削除しました。subjectの次に配置されます)
            $table->string('event')->nullable();
            // causer
            $table->nullableMorphs('causer', 'causer');
            // properties
            $table->json('properties')->nullable();
            // batch_uuid (afterを削除しました。propertiesの次に配置されます)
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();
            // インデックス
            $table->index('log_name');
        });
    }

    public function down()
    {
        Schema::connection(config('activitylog.database_connection'))->dropIfExists(config('activitylog.table_name'));
    }
}