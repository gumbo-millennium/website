<?php

declare(strict_types=1);

use App\Models\Activity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationTypeToActivities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activities', static function (Blueprint $table) {
            $table->string('location_type', 20)->default(Activity::LOCATION_OFFLINE)->after('location_address');
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
            $table->dropColumn('location_type');
        });
    }
}
