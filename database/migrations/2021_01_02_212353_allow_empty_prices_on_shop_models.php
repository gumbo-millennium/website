<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AllowEmptyPricesOnShopModels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shop_product_variants', static function (Blueprint $table) {
            $table->unsignedSmallInteger('price')
                ->default(null)
                ->nullable(true)
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
        Schema::table('shop_product_variants', static function (Blueprint $table) {
            $table->unsignedSmallInteger('price')
                ->default(0)
                ->nullable(false)
                ->change();
        });
    }
}
