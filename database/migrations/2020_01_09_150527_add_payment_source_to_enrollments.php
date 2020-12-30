<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentSourceToEnrollments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enrollments', static function (Blueprint $table) {
            // Add Invoice ID
            $table->string('payment_source')->nullable()->default(null)->after('payment_invoice');
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
            // Remove invoice ID
            $table->dropColumn('payment_source');
        });
    }
}
