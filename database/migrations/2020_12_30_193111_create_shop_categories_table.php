<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_categories', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();

            $table->string('name');
            $table->string('description')->nullable()->default(null);
            $table->string('slug', 64)->unique();

            $table->boolean('visible')->default(0);

            // Metadata
            $table->json('meta');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_categories');
    }
}
