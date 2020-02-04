<?php

declare(strict_types=1);

use App\Helpers\Arr;
use App\Models\NewsItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReadTimeToNewsItems extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        // Add field
        Schema::table('news_items', static function (Blueprint $table) {
            $table->string('read_time', 15)->nullable()->default(null)->after('contents');
        });

        // Update all items
        foreach (NewsItem::cursor() as $item) {
            $contents = json_decode($item->contents, true);
            Arr::set($contents, 'time', now());
            $item->contents = json_encode($contents);
            $item->save();
        }
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('news_items', static function (Blueprint $table) {
            $table->dropColumn('read_time');
        });
    }
}
