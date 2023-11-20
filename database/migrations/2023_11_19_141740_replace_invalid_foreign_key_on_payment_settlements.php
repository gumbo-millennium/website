<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // SQLite does not support dropping foreign keys, the schema was already changed.
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('payment_settlement_payment', function (Blueprint $table) {
            $table->dropForeign('payment_settlement_payment_payment_id_foreign');
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
        });
    }
};
