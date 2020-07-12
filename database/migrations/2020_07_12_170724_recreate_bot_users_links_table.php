<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RecreateBotUsersLinksTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        // noop if exists
        if (Schema::hasTable('bot_users_links')) {
            return;
        }

        // Create table
        Schema::create('bot_users_links', static function (Blueprint $table) {
            // Primary index
            $table->unsignedBigInteger('user_id');
            $table->string('driver', 16);
            $table->string('driver_id', 16);

            // Timestamps
            $table->timestamps();

            // Type and display name
            $table->string('type', 8);
            $table->string('name')->nullable()->default(null);

            // Constraints
            $table->primary(['user_id', 'driver']);
            $table->unique(['user_id', 'driver']);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bot_users_links');
    }
}
