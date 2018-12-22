<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds titles to permissions and roles, and adds a 'default' flag to roles.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class CreatePermissionTitles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableNames = config('permission.table_names');

        Schema::table($tableNames['permissions'], function (Blueprint $table) {
            $table->string('title')
                ->nullable()
                ->default(null)
                ->after('name');
        });

        Schema::table($tableNames['roles'], function (Blueprint $table) {
            $table->string('title')
                ->nullable()
                ->default(null)
                ->after('name');

            $table->boolean('default')
                ->default(false)
                ->after('guard_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableNames = config('permission.table_names');

        // Remove title from permissions
        Schema::table($tableNames['permissions'], function (Blueprint $table) {
            $table->dropColumn('title');
        });

        // Remove title from roles
        Schema::table($tableNames['roles'], function (Blueprint $table) {
            $table->dropColumn(['title', 'default']);
        });
    }
}
