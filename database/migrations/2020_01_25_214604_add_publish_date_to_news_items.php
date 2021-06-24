<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPublishDateToNewsItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('news_items', static function (Blueprint $table) {
            $field = $table
                ->dateTime('published_at')
                ->after('updated_at');

            if (DB::getDriverName() === 'sqlite') {
                $field->default('0000-00-00 00:00:00');
            } else {
                $field->useCurrent();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('news_items', static function (Blueprint $table) {
            $table->dropColumn('published_at');
        });
    }
}
