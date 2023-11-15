<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Delete the barely used payment_settlement_subject table.
 */
class DeletePaymentSettlementSubject extends Migration
{
    /**
     * Drop the table.
     */
    public function up(): void
    {
        Schema::dropIfExists('payment_settlement_subject');
    }
}
