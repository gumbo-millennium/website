<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoogleWalletMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('google_wallet_messages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('header');
            $table->text('body');

            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable()->default(null);

            $table->foreignId('activity_message_id')->constrained('activity_messages')->cascadeOnDelete();
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
