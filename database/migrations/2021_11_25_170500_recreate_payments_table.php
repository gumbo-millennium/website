<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RecreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuidMorphs('payable');
            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('provider', 20);
            $table->string('transaction_id', 180)->nullable();
            $table->unsignedSmallInteger('price')->comment('In cents');

            // Regular timestamps
            $table->timestamps();

            // Predictions
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->unique(['provider', 'transaction_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
