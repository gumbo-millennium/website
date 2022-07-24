<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebcamDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webcam_devices', function (Blueprint $table) {
            $table->id();

            $table->uuid('device');
            $table->string('name', 64);
            $table->string('path')->nullable();

            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();

            $table->foreignId('camera_id')
                ->nullable()
                ->constrained('webcam_cameras')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique([
                'device',
                'name',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('webcam_devices');
    }
}
