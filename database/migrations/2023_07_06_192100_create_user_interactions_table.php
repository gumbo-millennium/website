<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserInteractionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_interactions', function (Blueprint $table) {
            $table->id();

            $table->string('interaction', 30);
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->morphTo('model')->nullable();

            $table->timestamp('first_interaction');
            $table->timestamp('last_interaction');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('google_wallet_messages');
    }
}
