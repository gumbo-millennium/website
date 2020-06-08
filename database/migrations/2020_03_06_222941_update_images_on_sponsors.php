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
            // REMOVED when paperclip got removed
            // $table->dropPaperclip('image');

            // Add backdrop
            // REMOVED when paperclip got removed
            // $table->paperclip('backdrop');

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
            // REMOVED when paperclip got removed
            // $table->paperclip('image');

            // Drop backdrop and SVG paths
            // REMOVED when paperclip got removed
            // $table->dropPaperclip('backdrop');

            // Logo's
            $table->dropColumn(['logo_gray', 'logo_color']);
        });
    }
}
