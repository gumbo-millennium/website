<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTypoRecipientsInActivityMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activity_messages', function (Blueprint $table) {
            $table->renameColumn('receipients', 'recipients');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activity_messages', function (Blueprint $table) {
            $table->renameColumn('recipients', 'receipients');
        });
    }
}
