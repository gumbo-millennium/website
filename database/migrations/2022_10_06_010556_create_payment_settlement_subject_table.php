<?php

declare(strict_types=1);

use App\Models\Payments\Settlement;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentSettlementSubjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_settlement_subject', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignIdFor(Settlement::class, 'settlement_id')->constrained('payment_settlements')->cascadeOnDelete();
            $table->morphs('subject');

            $table->smallInteger('amount')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_settlement_subject');
    }
}
