<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix statement fields
 *
 * Funny story. UTF-8 in MySQL uses 4 bytes, but strings are counted in 3.
 * Effectively, this makes a string of 16 characters take up 21⅓ character.
 *
 * SCIENCE!!
 */
class FixStatementFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->string('statement', 22)
                ->nullable()
                ->default(null)
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->string('statement', 16)
                ->nullable()
                ->default(null)
                ->change();
        });
    }
}
