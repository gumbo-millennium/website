<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class ChangeNovaActionEventsIdsToStrings extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::table('action_events', static function ($table) {
            $table->string('actionable_id', 36)->change();
            $table->string('model_id', 36)->change();
            $table->string('target_id', 36)->change();
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        // Don't roll back, string → integer conversion is incompatible
    }
}
