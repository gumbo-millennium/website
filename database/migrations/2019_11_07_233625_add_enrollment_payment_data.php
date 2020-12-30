<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnrollmentPaymentData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enrollments', static function (Blueprint $table) {
            // Add price
            $table->unsignedSmallInteger('price')->nullable()->default(null)->after('deleted_reason');

            // Add Payment Intent ID
            $table->string('payment_intent')->nullable()->default(null)->after('price');

            // Remove Paid flag
            $table->dropColumn('paid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enrollments', static function (Blueprint $table) {
            // Drop columns
            $table->dropColumn(['price', 'payment_intent']);

            // Re-introduce prices
            $table
                ->boolean('paid')
                ->default(0)
                ->after('deleted_reason')
                ->comment('Indicates if the user has paid, in case the event price changes.');
        });
    }
}
