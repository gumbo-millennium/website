<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFileDownloadsTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('file_downloads', static function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamp('created_at')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('file_id');

            $table->ipAddress('ip');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');
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
