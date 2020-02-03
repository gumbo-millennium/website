<?php

declare(strict_types=1);

use App\Models\FileCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFileCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('file_categories', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('title')->nullable();
            $table->string('slug', 190)->unique();
        });

        // Ensure an 'other' category exists
        FileCategory::firstOrCreate(
            ['slug' => 'other'],
            ['title' => 'Overige']
        );
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_categories');
    }
}
