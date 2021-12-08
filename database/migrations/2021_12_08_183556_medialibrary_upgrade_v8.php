<?php

declare(strict_types=1);

use App\Helpers\Str;
use App\Models\Media;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MedialibraryUpgradeV8 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->uuid('uuid')->after('model_id')->nullable()->unique();
            $table->string('conversions_disk')->nullable()->after('disk');
        });

        Media::cursor()->each(fn (Media $media) => $media->update([
            'uuid' => Str::uuid(),
        ]));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn([
                'uuid',
                'conversions_disk',
            ]);
        });
    }
}
