<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', static function (Blueprint $table) {
            // IDs
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');

            // Timestamps
            $table->timestamps();
            $table->timestamp('paid_at')->nullable()->default(null);

            // Metadata
            $table->unsignedMediumInteger('total');
            $table->json('lines');
            $table->morphs('invoicable');
            $table->string('vendor', 10);
            $table->string('vendor_id')->nullable()->default(null);

            // Foreign
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
