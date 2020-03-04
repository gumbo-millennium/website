<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBotUserLinksTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('bot_user_links', static function (Blueprint $table) {
            // Key
            $table->uuid('id')->primary();

            // Indexes
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('driver', 16);
            $table->string('driver_id', 128);

            // Timestamps
            $table->timestamps();

            // Driver type and name
            $table->string('type', 8);
            $table->string('name', 180)->nullable()->default(null);

            // Indexes
            $table->unique(['driver', 'driver_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bot_user_links');
    }
}
