<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUserIdAndTypeFromBotUserLinks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Remove user ID with relation
        if (Schema::hasColumn('bot_user_links', 'user_id')) {
            Schema::table('bot_user_links', static function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }

        // Remove type
        if (Schema::hasColumn('bot_user_links', 'type')) {
            Schema::table('bot_user_links', static function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }

        // Remove timestamps
        if (! Schema::hasColumn('bot_user_links', 'created_at')) {
            return;
        }

        Schema::table('bot_user_links', static function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
}
