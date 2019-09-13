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
            $table->bigIncrements('id');
            $table->unsignedBigInteger('category_id')->nullable()->default(null);

            $table->timestamps();

            // Title and slug
            $table->string('title')->nullable()->default(null);
            $table->string('slug', 190)->unique();

            // The actual file, without 'variants'
            $table->paperclip('file', false);

            // File contents, for full-text search
            $table->mediumText('file_contents')->nullable()->default(null);
            $table->unsignedSmallInteger('file_pages')->nullable()->default(null);
            $table->json('file_meta')->nullable()->default(null);

            // Additional data
            $table->boolean('pulled')->default(0);
            $table->unsignedBigInteger('replacement_id')->nullable()->default(null);

            // Extra meta
            $table->paperclip('thumbnail');

            // Owner meta
            $table->unsignedBigInteger('owner_id')->nullable()->default(null);

            // Foreign
            $table->foreign('replacement_id')->references('id')->on('files')->onDelete('cascade');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('file_categories')->onDelete('cascade');
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
