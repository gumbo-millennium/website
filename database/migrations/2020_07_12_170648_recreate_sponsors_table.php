<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RecreateSponsorsTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        // noop if exists
        if (Schema::hasTable('sponsors')) {
            return;
        }

        // Get edge cases
        $isSqlite = DB::getDriverName() === 'sqlite';

        // Get page query
        $hasPageQuery = 'JSON_CONTAINS_PATH(`contents`, \'one\', "$.blocks")';
        if ($isSqlite) {
            $hasPageQuery = '(json_type(`contents`, \'$.blocks\') != NULL)';
        }

        // Create table
        Schema::create('sponsors', static function (Blueprint $table) use ($hasPageQuery, $isSqlite) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->softDeletes();

            // Basic meta
            $table->string('name');
            $table->string('slug', 60)->default('')->unique();
            $table->string('url');

            // Add caption for the site-wide adverts
            $table->text('caption')->nullable()->default(null);

            // Sponsor page contents
            $table->string('contents_title')->nullable()->default(null);
            $table->json('contents')->nullable();

            // Artwork
            $table->string('backdrop')->nullable()->default(null);
            $table->string('logo_gray')->nullable()->default(null);
            $table->string('logo_color')->nullable()->default(null);

            // Contract dates
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            // Flags
            $field = $table->boolean('has_page')->storedAs($hasPageQuery);
            if ($isSqlite) {
                $field->default('0');
            }

            // Counter
            $table->integer('view_count')->unsigned()->default(0)->comment('Number of showings');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sponsors');
    }
}
