<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::update(<<<'SQL'
            UPDATE users
            SET last_seen_at = updated_at
            SQL);
    }
};
