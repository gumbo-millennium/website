<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalDataToPaymentSettlements extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_settlements', function (Blueprint $table) {
            $table->integer('amount')->change();
        });

        Schema::table('payment_settlements', function (Blueprint $table) {
            $table->after('amount', function (Blueprint $table) {
                $table->integer('fees');
                $table->json('missing_payments');
                $table->json('missing_refunds');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_settlements', function (Blueprint $table) {
            $table->dropColumn([
                'fees',
                'missing_payments',
                'missing_refunds',
            ]);
        });
    }
}
