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
        Schema::create('bot_quote_reactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('quote_id')->constrained('bot_quotes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->string('reaction', 100)->nullable();

            $table->timestamps();

            $table->unique([
                'quote_id',
                'user_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_quote_reactions');
    }
};
