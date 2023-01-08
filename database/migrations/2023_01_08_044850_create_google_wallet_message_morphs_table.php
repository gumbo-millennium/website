<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoogleWalletMessageMorphsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('google_wallet_message_morphs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('message_id')->constrained('google_wallet_messages')->cascadeOnDelete();
            $table->morphs('object');

            $table->unique(['message_id', 'object_id', 'object_type'], 'google_wallet_message_morphs_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('google_wallet_message_morphs');
    }
}
