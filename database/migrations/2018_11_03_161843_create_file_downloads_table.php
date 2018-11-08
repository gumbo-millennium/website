<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFileDownloadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_downloads', function (Blueprint $table) {
            // Index
            $table->uuid('id')->primary();

            // User and file
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('file_id');

            // meta
            $table->timestamp('downloaded_at')
                ->nullable()
                ->comment('Time of download');
            $table->ipAddress('ip')
                ->nullable()
                ->comment('IP address of download');

            $table->index(['user_id', 'file_id']);
        });

        Schema::table('file_downloads', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('set null');

            $table->foreign('file_id')
                ->references('id')->on('files')
                ->onDelete('cascade');
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
