<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePaperclipColumns extends Migration
{
    private const TABLE_MAPPING = [
        'activities' => [
            'image',
        ],
        'news_items' => [
            'image',
        ],
        'pages' => [
            'image',
        ],
        'sponsors' => [
            'backdrop',
        ],
    ];

    private const PAPERCLIP_FIELDS = [
        'file_name',
        'file_size',
        'content_type',
        'updated_at',
        'variants',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (self::TABLE_MAPPING as $table => $columns) {
            foreach ($columns as $column) {
                foreach (self::PAPERCLIP_FIELDS as $field) {
                    $columnField = "{$column}_${field}";

                    if (Schema::hasColumn($table, $columnField)) {
                        /** @var Blueprint $blueprint */
                        Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropColumn($columnField));
                    }
                }
            }
        }
    }
}
