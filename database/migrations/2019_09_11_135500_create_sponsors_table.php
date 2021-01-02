<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSponsorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sponsors', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            // Required fields
            $table->string('name')->comment('Sponsor name');
            $table->string('url')->comment('URL of sponsor landing page');

            $table->paperclip('image');

            // Visiblity dates
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            // Click counter
            $table->integer('view_count')->unsigned()->default(0)->comment('Number of showings');
            $table->integer('click_count')->unsigned()->default(0)->comment('Number of clicks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sponsors');
    }
}
