<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsItemsTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        // Create database
        Schema::create('news_items', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            // Title of the page and the URL
            $table->string('title');
            $table->string('slug')->unique();

            // Contents of the page, as JSON
            $table->json('contents')->nullable();

            // User who last edited the page
            $table->unsignedBigInteger('author_id')->nullable()->index();

            // Add relation
            $table->foreign('author_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('news_items');
    }
}
