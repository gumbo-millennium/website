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
            // Use UUIDs
            $table->uuid('id')->primary();

            // Basically a composite key, but Laravel is iffey about it
            $table->string('provider', 10);
            $table->string('provider_id', 64);

            // Enrollment this invoice is for
            $table->foreignUuid('enrollment_id');

            // Amount to get
            $table->smallInteger('amount');

            // Flags
            $table->boolean('paid')->default(0);
            $table->boolean('refunded')->default(0);

            // Blob
            $table->json('meta');

            // One invoice per, well, invoice
            $table->unique(['provider', 'provider_id']);
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
