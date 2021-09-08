<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderLimitToShopProductVariantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shop_product_variants', function (Blueprint $table) {
            $table->unsignedTinyInteger('order_limit')->nullable()->default(null)->after('price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shop_product_variants', function (Blueprint $table) {
            $table->dropColumn('order_limit');
        });
    }
}
