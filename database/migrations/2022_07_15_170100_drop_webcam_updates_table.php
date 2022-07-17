<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropWebcamUpdatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('webcam_updates');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('webcam_updates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('webcam_id');

            $table->string('ip');
            $table->string('user_agent');

            $table->string('path')->nullable()->default(null);

            $table->timestamps();

            $table->foreign('webcam_id')->references('id')->on('webcams')->onDelete('cascade');
        });
    }
}
