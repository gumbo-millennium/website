<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeActionEventIdsToStrings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Only run when Nova is actually installed
        if (! Schema::hasTable('action_events')) {
            return;
        }

        // Mutate
        Schema::table('action_events', static function (Blueprint $table) {
            $table->string('actionable_id', 36)->change();
            $table->string('model_id', 36)->change();
            $table->string('target_id', 36)->change();
            $table->string('user_id', 36)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Only run when Nova is actually installed
        if (! Schema::hasTable('action_events')) {
            return;
        }

        // Mutate
        Schema::table('action_events', static function (Blueprint $table) {
            $table->integer('actionable_id')->unsigned()->change();
            $table->integer('model_id')->unsigned()->change();
            $table->integer('target_id')->unsigned()->change();
            $table->integer('user_id')->unsigned()->change();
        });
    }
}
