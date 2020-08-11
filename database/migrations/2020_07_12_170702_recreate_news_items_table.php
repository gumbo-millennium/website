<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RecreateNewsItemsTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        // noop if exists
        if (Schema::hasTable('news_items')) {
            return;
        }

        // Create table
        Schema::create('news_items', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->timestamp('published_at')->useCurrent();

            // Title of the page and the URL
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category', 30)->default('Nieuws');

            // Sponsor
            $table->string('sponsor')->nullable();

            // Headline and contents
            $table->string('headline')->nullable()->default(null);
            $table->json('contents')->nullable();

            // Art and meta
            $table->string('image')->nullable()->default(null);
            $table->string('read_time', 15)->nullable()->default(null);
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
