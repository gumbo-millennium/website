<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFileBundlesTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('file_bundles', static function (Blueprint $table) {
            // ID and parent
            $table->bigIncrements('id');
            $table->unsignedBigInteger('category_id')->nullable()->default(null);

            // Timestamps
            $table->timestamps();
            $table->timestamp('published_at')->useCurrent();

            // Title and slug
            $table->unsignedBigInteger('owner_id')->nullable()->default(null);
            $table->string('title');
            $table->string('slug', 190)->unique();

            // Meta
            $table->text('description')->nullable()->default(null);
            $table->unsignedInteger('total_size')->default(0);

            // Foreign keys
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_bundles');
    }
}
