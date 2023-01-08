<?php

declare(strict_types=1);

use App\Models\ActivityMessage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeActivityMessageAudienceToBoolean extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activity_messages', function (Blueprint $table) {
            $table->boolean('include_pending')->default(0)->after('target_audience');
        });

        DB::table('activity_messages')->whereIn('target_audience', [
            ActivityMessage::AUDIENCE_ANY,
            ActivityMessage::AUDIENCE_PENDING,
        ])->update(['include_pending' => 1]);

        Schema::table('activity_messages', function (Blueprint $table) {
            $table->dropColumn('target_audience');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activity_messages', function (Blueprint $table) {
            $table->string('target_audience')->after('include_pending');
        });

        DB::table('activity_messages')
            ->where('include_pending', true)
            ->update(['target_audience' => ActivityMessage::AUDIENCE_ANY]);

        Schema::table('activity_messages', function (Blueprint $table) {
            $table->dropColumn('include_pending');
        });
    }
}
