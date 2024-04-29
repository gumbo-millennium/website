<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conscribo_committees', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('conscribo_id')->unique();

            $table->string('name');
            $table->string('email');
            $table->json('aliases');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conscribo_committees');
    }
};