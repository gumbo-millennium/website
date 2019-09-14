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
            // IDs
            $table->uuid('id')->primary();
            $table->string('transaction_id', 180)->unique();
            $table->unsignedBigInteger('user_id');
            $table->uuid('enrollment_id');

            // Timestamps, for creation, update and completion
            $table->timestamps();
            $table->dateTime('completed_at')->nullable()->default(null);

            // Status of the payment, free text
            $table->string('status', 20);

            // Payment and refund amount stored in cents
            $table->unsignedInteger('amount')->comment('In cents');

            // Refund information
            $table->dateTime('refunded_at')->nullable()->default(null);
            $table->unsignedInteger('refunded_amount')->nullable()->default(null);

            // The payment data
            $table->json('data')->nullable()->default(null);

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('enrollment_id')->references('id')->on('enrollments')->onDelete('restrict');
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
