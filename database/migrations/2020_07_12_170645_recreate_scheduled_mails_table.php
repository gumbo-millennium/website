<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RecreateScheduledMailsTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        // noop if exists
        if (Schema::hasTable('scheduled_mails')) {
            return;
        }

        $isSentQuery = 'ISNULL(`sent_at`)';
        if (DB::getDriverName() === 'sqlite') {
            $isSentQuery = '(`sent_at` == NULL)';
        }

        // Create table
        Schema::create('scheduled_mails', static function (Blueprint $table) use ($isSentQuery) {

            $table->bigIncrements('id');

            // Index
            $table->string('group', 64);
            $table->string('name', 50);
            $table->unique('group', 'name');

            // Dates
            $table->timestamp('scheduled_for');
            $table->timestamp('sent_at')->nullable()->default(null);

            // Virtuals
            $table->boolean('is_sent')->virtualAs($isSentQuery);
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
