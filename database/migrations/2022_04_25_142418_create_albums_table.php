<?php

declare(strict_types=1);

use App\Enums\AlbumVisibility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlbumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('albums', function (Blueprint $table) {
            $table->id();

            $table->foreignId('activity_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->string('slug')->unique();

            $table->string('description')->nullable();

            $table->string('visibility', 20)->default(AlbumVisibility::Private->value);

            $table->timestamps();
            $table->softDeletes();

            $table->timestamp('editable_from')->nullable();
            $table->timestamp('editable_until')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('albums');
    }
}
