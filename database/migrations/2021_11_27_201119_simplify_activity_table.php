<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SimplifyActivityTable extends Migration
{
    private const COLUMNS_TO_DROP = [
        'statement',
        'member_discount',
        'discount_count',
        'stripe_coupon_id',
        'price',
        'payment_type',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use a foreach loop on SQLite (one deletion per query)
        if (DB::getDriverName() === 'sqlite') {
            $this->removeLoopedQuery();

            return;
        }

        $this->removeSingleQuery();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse everything as a string
        Schema::table('activities', function (Blueprint $table) {
            foreach (self::COLUMNS_TO_DROP as $column) {
                $table->string($column)->nullable();
            }
        });
    }

    /**
     * Remove fields using a separate query for each field.
     */
    private function removeLoopedQuery(): void
    {
        foreach (self::COLUMNS_TO_DROP as $column) {
            if (! Schema::hasColumn('activity', $column)) {
                continue;
            }

            Schema::table('activities', function (Blueprint $table) use ($column) {
                $table->dropColumn($column);
            });
        }
    }

    /**
     * Remove fields using a single query.
     */
    private function removeSingleQuery(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            foreach (self::COLUMNS_TO_DROP as $column) {
                if (! Schema::hasColumn('activities', $column)) {
                    continue;
                }

                $table->dropColumn($column);
            }
        });
    }
}
