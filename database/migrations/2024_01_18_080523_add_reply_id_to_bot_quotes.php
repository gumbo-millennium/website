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
        Schema::table('bot_quotes', function (Blueprint $table) {
            $table->string('reply_id')->nullable()->after('message_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bot_quotes', function (Blueprint $table) {
            $table->dropColumn('reply_id');
        });
    }
};
