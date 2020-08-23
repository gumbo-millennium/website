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
            $table->string('platform', 10);
            $table->string('platform_id', 64);
            $table->foreignUuid('enrollment_id');
            $table->smallInteger('amount');
            $table->boolean('paid')->default(0);
            $table->boolean('refunded')->default(0);
            $table->json('meta');
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
