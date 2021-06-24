<?php

declare(strict_types=1);

use App\Models\Sponsor;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToSponsors extends Migration
{
    private const PAGE_SQL = <<<'SQL'
        JSON_CONTAINS_PATH(`contents`, 'one', "$.blocks")
    SQL;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add slugs
        Schema::table('sponsors', static function (Blueprint $table) {
            // Add caption for the site-wide adverts
            $table->text('caption')->nullable()->default(null)->after('backdrop_variants');

            // Add has_page as computed field
            $table->string('contents_title')->nullable()->default(null);
            $table->json('contents')->nullable();
            $hasPage = $table->boolean('has_page')->storedAs(self::PAGE_SQL)->after('ends_at');

            // Add soft deletes and slug
            $table->softDeletes()->after('updated_at');
            $table->string('slug', 60)->default('')->after('name');

            if (DB::getDriverName() === 'sqlite') {
                $hasPage->default(false);
            }
        });

        // Auto-update slug by assigning random value, setting it to null
        // and letting Eloquent sluggable handle it
        foreach (Sponsor::get(['id', 'name']) as $sponsor) {
            $sponsor->slug = Str::uuid();
            $sponsor->syncOriginalAttribute('slug');
            $sponsor->slug = null;
            $sponsor->save(['slug']);
        }

        // Add unique constraint
        Schema::table('sponsors', static function (Blueprint $table) {
            $table->unique('slug');
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
            $table->dropSoftDeletes();
            $table->dropColumn([
                'caption',
                'contents_title',
                'contents',
                'has_page',
                'slug',
            ]);
        });
    }
}
