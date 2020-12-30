<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPostponedFieldToActivities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activities', static function (Blueprint $table) {
            $table->timestamp('postponed_at')->nullable()->default(null)->after('rescheduled_reason');
            $table->string('postponed_reason')->nullable()->default(null)->after('postponed_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activities', static function (Blueprint $table) {
            $table->dropColumn(['postponed_at', 'postponed_reason']);
        });
    }
}
