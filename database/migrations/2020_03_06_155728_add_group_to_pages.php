<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGroupToPages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pages', static function (Blueprint $table) {
            $table->string('group', 20)->nullable()->default(null)->after('slug');

            $table->dropUnique(['slug']);
            $table->unique(['group', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Ensure the slugs are unique
        DB::update(<<<'SQL'
            UPDATE
                `pages`
            SET
                `slug` = CONCAT(`group`, '--', `slug`)
            WHERE
                `group` IS NOT NULL
        SQL);

        // Drop the group column and update unique constraint accordingly
        Schema::table('pages', static function (Blueprint $table) {
            $table->dropUnique(['group', 'slug']);
            $table->dropColumn('group');

            $table->unique('slug');
        });
    }
}
