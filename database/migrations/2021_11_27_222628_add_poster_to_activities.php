<?php

declare(strict_types=1);

use App\Models\Activity;
use Database\Migrations\Traits\MigratesPaperclipAttachments;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPosterToActivities extends Migration
{
    use MigratesPaperclipAttachments;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->string('poster')->nullable()->after('description');
        });

        $this->migrateAttachments(Activity::class, 'image', 'poster');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('poster');
        });
    }
}
