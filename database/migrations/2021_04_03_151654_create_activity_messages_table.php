<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_messages', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('sender_id')->nullable();

            $table->string('target_audience');
            $table->string('subject');
            $table->text('body');

            $table->unsignedSmallInteger('receipients')->nullable()->default(null);

            $table->timestamps();
            $table->timestamp('sent_at')->nullable()->default(null);

            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activity_messages');
    }
}
