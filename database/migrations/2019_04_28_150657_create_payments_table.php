<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
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
            $table->timestamps();

            // Base dates
            $table->dateTime('completed_at')->nullable()->default(null);
            $table->dateTime('refunded_at')->nullable()->default(null);

            // The payment provider
            $table->string('provider', 20)
                ->comment('The provider name');

            // Add payment ID
            $table->string('provider_id', 128)
                ->comment('The ID of the payment')
                ->nullable()
                ->default(null);

            $table->string('status', 20)
                ->comment('Payment status');

            // Payment and refund amount stored in cents
            $table->unsignedInteger('amount')
                ->comment('Paid amount, in cents');
            $table->unsignedInteger('refund_amount')
                ->comment('Price refunded, in cents')
                ->nullable()
                ->default(null);


            // The payment data
            $table->string('data')
                ->comment('Payment data')
                ->nullable()
                ->default(null);

            // Add unique index
            $table->unique(['provider', 'provider_id']);
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
