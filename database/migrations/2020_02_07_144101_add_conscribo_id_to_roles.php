<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConscriboIdToRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles', static function (Blueprint $table) {
            $table->unsignedSmallInteger('conscribo_id')
                ->nullable()
                ->default(null)
                ->after('default');

            $table->unique('conscribo_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('roles', static function (Blueprint $table) {
            $table->dropColumn('conscribo_id');
        });
    }
}
