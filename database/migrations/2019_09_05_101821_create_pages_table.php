<?php

use App\Models\Page;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create database
        Schema::create('pages', function (Blueprint $table) {
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

        // Ensure some pages exist
        foreach (Page::REQUIRED_PAGES as $slug => $title) {
            Page::firstOrCreate([
                'slug' => $slug
            ], [
                'title' => $title,
                'contents' => null
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pages');
    }
}
