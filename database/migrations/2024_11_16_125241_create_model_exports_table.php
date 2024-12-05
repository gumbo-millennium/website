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
        Schema::create('model_exports', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->morphs('model');
            $table->foreignId('user_id')->nullable()->references('id')->on('users')->nullOnDelete();

            $table->string('job');

            $table->string('disk')->nullable();
            $table->string('path')->nullable();
            $table->string('name')->nullable();

            $table->unique(['model_id', 'model_type'], 'un_model_exports_model');
            $table->index('user_id', 'in_model_exports_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_exports');
    }
};
