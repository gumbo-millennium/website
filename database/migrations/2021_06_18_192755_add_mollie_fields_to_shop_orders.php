<?php

declare(strict_types=1);

use App\Models\Shop\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMollieFieldsToShopOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->string('number', 20)->after('id');

            $table->timestamp('expires_at')->nullable()->default(null)->after('updated_at');

            $table->string('payment_id')->nullable()->default(null)->after('user_id');

            $table->unsignedSmallInteger('fee')->default(0)->after('price');
        });

        foreach (Order::cursor() as $order) {
            $order->number = Order::determineOrderNumber($order);
            $order->save();
        }

        Schema::table('shop_orders', function (Blueprint $table) {
            $table->unique(['number']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->dropUnique(['number']);

            $table->dropColumn([
                'number',
                'payment_id',
                'expires_at',
                'fee',
            ]);
        });
    }
}
