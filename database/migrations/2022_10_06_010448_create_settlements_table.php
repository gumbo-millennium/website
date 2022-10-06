<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettlementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_settlements', function (Blueprint $table) {
            // Identifiers
            $table->id();
            $table->string('mollie_id')->unique();
            $table->string('reference')->nullable()->unique();

            // Metadata
            $table->string('status')->default('open');
            $table->unsignedSmallInteger('amount');

            // Files
            $table->string('export_path')->nullable();

            // Timestamps
            $table->timestamps();
            $table->timestamp('settled_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_settlements');
    }
}
