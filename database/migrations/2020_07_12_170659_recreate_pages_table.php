<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RecreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        // noop if exists
        if (Schema::hasTable('pages')) {
            return;
        }

        // Create table
        Schema::create('pages', static function (Blueprint $table) {
            // ID and timestamps
            $table->bigIncrements('id');
            $table->timestamps();

            // Title of the page, the URL and the group
            $table->string('title');
            $table->string('slug');
            $table->string('group', 20)->nullable()->default(null);

            // Type and contents
            $table->string('summary', 120)->nullable()->default(null);
            $table->json('contents')->nullable();

            // Art and meta
            $table->paperclip('image');
            $table->string('type', 10)->default('user');

            // User who last edited the page
            $table->unsignedBigInteger('author_id')->nullable()->index();

            // Constraints
            $table->unique(['group', 'slug']);

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
        Schema::dropIfExists('pages');
    }
}
