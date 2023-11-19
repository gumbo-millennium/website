<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReplaceInvalidForeignKeyOnPaymentSettlements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_settlement_payment', function (Blueprint $table) {
            $table->dropForeign('payment_settlement_payment_payment_id_foreign');
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
        });
    }
}
