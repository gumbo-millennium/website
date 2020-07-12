<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RecreateFileDownloadsTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        // noop if exists
        if (Schema::hasTable('file_downloads')) {
            return;
        }

        // Create table
        Schema::create('file_downloads', static function (Blueprint $table) {
            // ID and basic timestamp
            $table->uuid('id');
            $table->timestamp('created_at')->nullable();

            // Connections
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('bundle_id');
            $table->unsignedBigInteger('media_id')->nullable()->default(null);

            // Extra metadata
            $table->ipAddress('ip');
            $table->string('user_agent');

            // Constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('bundle_id')->references('id')->on('file_bundles')->onDelete('cascade');
            $table->foreign('media_id')->references('id')->on('media')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_downloads');
    }
}
