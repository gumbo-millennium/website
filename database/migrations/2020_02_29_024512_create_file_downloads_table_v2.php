<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFileDownloadsTableV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_downloads', static function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamp('created_at')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('bundle_id');
            $table->unsignedBigInteger('media_id')->nullable()->default(null);

            $table->ipAddress('ip');
            $table->string('user_agent');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('bundle_id')->references('id')->on('file_bundles')->onDelete('cascade');
            $table->foreign('media_id')->references('id')->on('media')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_downloads');
    }
}
