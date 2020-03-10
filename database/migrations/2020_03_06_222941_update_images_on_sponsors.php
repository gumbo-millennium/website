<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateImagesOnSponsors extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::table('sponsors', static function (Blueprint $table) {
            // Remove image
            $table->dropPaperclip('image');

            // Add backdrop
            $table->paperclip('backdrop');

            // Add SVG paths
            $table->string('logo_gray')->nullable()->default(null);
            $table->string('logo_color')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('sponsors', static function (Blueprint $table) {
            // Re-add image
            $table->paperclip('image');

            // Drop backdrop and SVG paths
            $table->dropPaperclip('backdrop');
            $table->dropColumn(['logo_gray', 'logo_color']);
        });
    }
}
