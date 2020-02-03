<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeActivityMemberPriceToDiscount extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        // Move column
        Schema::table('activities', static function (Blueprint $table) {
            // Change price to discount
            $table->renameColumn('price_member', 'member_discount');
            $table->renameColumn('price_guest', 'price');
        });

        // Then add count
        Schema::table('activities', static function (Blueprint $table) {
            // Add max count
            $table->unsignedTinyInteger('discount_count')->nullable()->default(null)->after('member_discount');
        });

        // Then re-calculate values
        DB::update(<<<'SQL'
        UPDATE `activities`
        SET `member_discount` = `price` - `member_discount`
        WHERE `member_discount` IS NOT NULL
        SQL);
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('activities', static function (Blueprint $table) {
            $table->renameColumn('member_discount', 'price_member');
            $table->renameColumn('price', 'price_guest');
            $table->dropColumn('discount_count');
        });

        DB::update(<<<'SQL'
        UPDATE `activities`
        SET `price_member` = `price_guest` - `price_member`
        WHERE `price_member` IS NOT NULL
        SQL);
    }
}
