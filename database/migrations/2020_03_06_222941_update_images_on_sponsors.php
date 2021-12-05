<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateImagesOnSponsors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sponsors', static function (Blueprint $table) {
            // Add SVG paths
            $table->string('logo_gray')->nullable()->default(null);
            $table->string('logo_color')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sponsors', static function (Blueprint $table) {
            // Drop SVG paths
            $table->dropColumn(['logo_gray', 'logo_color']);
        });
    }
}
