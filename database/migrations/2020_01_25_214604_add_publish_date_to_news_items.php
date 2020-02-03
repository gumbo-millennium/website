<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPublishDateToNewsItems extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::table('news_items', static function (Blueprint $table) {
            $table->dateTime('published_at')->useCurrent()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('news_items', static function (Blueprint $table) {
            $table->dropColumn('published_at');
        });
    }
}
