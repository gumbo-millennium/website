<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopProductVariantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_product_variants', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->timestamps();

            $table->string('name');
            $table->string('description')->nullable()->default(null);
            $table->string('slug', 64);

            $table->string('image_url')->nullable()->default(null);
            $table->string('sku')->nullable()->default(null);
            $table->unsignedSmallInteger('price')->default(0);

            $table->json('options');

            $table->foreign('product_id')->references('id')->on('shop_products')->onDelete('cascade');

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
        Schema::dropIfExists('shop_product_variants');
    }
}
