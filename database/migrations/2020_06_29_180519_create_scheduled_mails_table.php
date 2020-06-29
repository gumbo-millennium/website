<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduledMailsTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('scheduled_mails', static function (Blueprint $table) {
            $table->bigIncrements('id');

            // Index
            $table->string('group', 64);
            $table->string('name', 50);
            $table->unique('group', 'name');

            // Dates
            $table->timestamp('scheduled_for');
            $table->timestamp('sent_at')->nullable()->default(null);

            // Virtuals
            $table->boolean('is_sent')->virtualAs('ISNULL(`sent_at`)');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scheduled_mails');
    }
}
