<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeRedirectInstructionsToLowercase extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::update('UPDATE redirect_instructions SET slug = LOWER(slug)');
    }
}
