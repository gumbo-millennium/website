<?php

declare(strict_types=1);

use App\Enums\Models\BarcodeType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBarcodeDataToEnrollments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activity_enrollments', function (Blueprint $table) {
            $table->renameColumn('ticket_code', 'barcode');
        });

        Schema::table('activity_enrollments', function (Blueprint $table) {
            $table->string('barcode', 32)->nullable()->change();
        });

        Schema::table('activity_enrollments', function (Blueprint $table) {
            $table->after('barcode', function ($table) {
                $table->string('barcode_type')->default(BarcodeType::QRCODE->value);
                $table->boolean('barcode_generated')->default(false);
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activity_enrollments', function (Blueprint $table) {
            $table->renameColumn('barcode', 'ticket_code');
            $table->dropColumn('barcode_type');
            $table->dropColumn('barcode_generated');
        });
    }
}
