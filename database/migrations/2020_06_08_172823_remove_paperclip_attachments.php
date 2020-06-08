<?php

declare(strict_types=1);

use App\Helpers\Arr;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePaperclipAttachments extends Migration
{
    private const TABLE_MAP = [
        'activities' => 'image',
        'news_items' => 'image',
        'pages' => 'image',
        'sponsors' => ['backdrop', 'image'],
    ];

    private const PAPERCLIP_COLUMNS = [
        'file_name',
        'file_size',
        'content_type',
        'updated_at',
        'variants'
    ];

    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        // Iterate over tables
        foreach (self::TABLE_MAP as $table => $fields) {
            $taskList = [];
            $fields = Arr::wrap($fields);

            // Check each field
            foreach ($fields as $field) {
                // Check each composed field from Paperclip
                foreach (self::PAPERCLIP_COLUMNS as $fieldName) {
                    // Build name and check if it exists
                    $columnName = "{$field}_{$fieldName}";
                    if (Schema::hasColumn($table, $columnName)) {
                        $taskList[] = $columnName;
                    }
                }
            }

            // Drop all matched columns from the task list
            Schema::table(
                $table,
                static fn (Blueprint $table) => $table->dropColumn($taskList)
            );
        }
    }
}
