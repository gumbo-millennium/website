<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToPages extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::table('pages', static function (Blueprint $table) {
            $table->string('type', 10)
                ->default('user')
                ->after('slug');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('pages', static function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
