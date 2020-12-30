<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_products', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('category_id')->nullable()->default(null);
            $table->timestamps();

            $table->string('name');
            $table->string('description')->nullable()->default(null);
            $table->string('slug', 64)->unique();

            $table->string('image_url')->nullable()->default(null);
            $table->string('etag')->nullable()->default(null);
            $table->unsignedTinyInteger('vat_rate')->default(21);

            $table->boolean('visible')->default(0);

            $table->foreign('category_id')->references('id')->on('shop_categories')->onDelete('set null');

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
        Schema::dropIfExists('shop_products');
    }
}
