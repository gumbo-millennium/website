<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddGeneratedNameToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', static function (Blueprint $table) {
            $column = $table->string('name')
                ->virtualAs('CONCAT_WS(" ", `first_name`, `insert`, `last_name`)')
                ->after('last_name');

            if (DB::getDriverName() === 'sqlite') {
                $column->default('');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', static function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
}
