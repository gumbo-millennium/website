<?php

declare(strict_types=1);

use App\Models\NewsItem;
use Database\Migrations\Traits\MigratesPaperclipAttachments;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoverToNewsItems extends Migration
{
    use MigratesPaperclipAttachments;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('news_items', function (Blueprint $table) {
            $table->string('cover')->nullable()->after('slug');
        });

        $this->migrateAttachments(NewsItem::class, 'image', 'cover');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('news_items', function (Blueprint $table) {
            $table->dropColumn('cover');
        });
    }
}
