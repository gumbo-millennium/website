<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBarcodeTypeToGoogleWalletEventObjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('google_wallet_event_objects', function (Blueprint $table) {
            $table->string('barcode_type')->after('barcode')->default('qrcode');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('google_wallet_event_objects', function (Blueprint $table) {
            $table->dropColumn('barcode_type');
        });
    }
}
