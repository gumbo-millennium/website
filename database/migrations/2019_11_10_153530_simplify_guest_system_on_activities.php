<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SimplifyGuestSystemOnActivities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activities', static function (Blueprint $table) {
            $table->boolean('is_public')->default(1)->after('seats');
        });

        Schema::table('activities', static function (Blueprint $table) {
            $table->dropColumn('public_seats');
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
            $table->dropColumn('is_public');
            $table->unsignedTinyInteger('public_seats')->nullable()->default(null)->after('seats');
        });
    }
}
