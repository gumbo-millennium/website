<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RecreateFileCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        // noop if exists
        if (Schema::hasTable('file_categories')) {
            return;
        }

        // Create table
        Schema::create('file_categories', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('title')->nullable();
            $table->string('slug', 190)->unique();
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_categories');
    }
}
