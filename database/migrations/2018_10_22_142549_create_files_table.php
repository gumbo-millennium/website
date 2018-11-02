<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('slug', 190)->unique()->comment('User-specified slug');
            $table->string('title')->nullable()->default(null)->comment('Title of the file');

            // Original filename
            $table->string('filename')->comment('Original filename, as uploaded');
            $table->integer('filesize')->comment('File size, in bytes');
            $table->string('mime')->nullable()->default(null)->comment('File mime type');

            // Storage path and publicity
            $table->string('path')->comment('Filesystem path');
            $table->boolean('public')->default(false)->comment('Is the file public?');

            // Indexing fields
            $table->mediumText('contents')->nullable()->default(null)->comment('OCR-translated contents');

            // Extra meta
            $table->string('thumbnail')->nullable()->default(null)->comment('Path of the thumbnail');
            $table->integer('page_count')->nullable()->default(null)->comment('Number of pages');

            // Owner meta
            $table->integer('owner_id')->unsigned()->nullable()->comment('User owning the file');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
}
