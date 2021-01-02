<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopOrderProductVariantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_order_product_variant', static function (Blueprint $table) {
            $table->uuid('product_variant_id');
            $table->unsignedBigInteger('order_id');

            $table->unsignedSmallInteger('price');
            $table->unsignedTinyInteger('vat_rate')->default(21);

            $table->primary([
                'product_variant_id',
                'order_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_order_product_variant');
    }
}
