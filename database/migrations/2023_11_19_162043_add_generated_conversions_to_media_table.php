<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Required for the upgrade of laravel-medialibrary.
 *
 * @link <https://github.com/spatie/laravel-medialibrary/blob/9d92e72a9e46cf1d7462cb48a589dd28b9a5703e/UPGRADING.md#from-v8-to-v9>
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('media', 'generated_conversions')) {
            Schema::table('media', function (Blueprint $table) {
                $table->json('generated_conversions')->nullable();
            });
        }

        // Don't upgrade SQLite databases, they're used for testing only
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Media::query()
            ->where(function ($query) {
                $query->whereNull('generated_conversions')
                    ->orWhere('generated_conversions', '')
                    ->orWhereRaw("JSON_TYPE(generated_conversions) = 'NULL'");
            })
            ->whereRaw('JSON_LENGTH(custom_properties) > 0')
            ->update([
                'generated_conversions' => DB::raw("JSON_EXTRACT(custom_properties, '$.generated_conversions')"),
                // OPTIONAL: Remove the generated conversions from the custom_properties field as well:
                // 'custom_properties'     => DB::raw("JSON_REMOVE(custom_properties, '$.generated_conversions')")
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /* Restore the 'generated_conversions' field in the 'custom_properties' column if you removed them in this migration
        Media::query()
                ->whereRaw("JSON_TYPE(generated_conversions) != 'NULL'")
                ->update([
                    'custom_properties' => DB::raw("JSON_SET(custom_properties, '$.generated_conversions', generated_conversions)")
                ]);
        */

        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('generated_conversions');
        });
    }
};
