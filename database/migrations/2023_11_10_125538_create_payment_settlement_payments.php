<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentSettlementPayments extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_settlement_payment', function (Blueprint $table) {
            $table->id();

            $table->foreignId('settlement_id')->constrained('payment_settlements')->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained('payment_settlements')->restrictOnDelete();

            $table->integer('amount');

            $table->unique(['settlement_id', 'payment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_settlement_payment');
    }
}
