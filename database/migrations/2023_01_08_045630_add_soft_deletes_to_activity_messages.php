<?php

declare(strict_types=1);

use App\Models\ActivityMessage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeletesToActivityMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activity_messages', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        ActivityMessage::query()
            ->whereNotNull('deleted_at')
            ->forceDelete();

        Schema::table('activity_messages', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
