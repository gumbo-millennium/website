<?php

declare(strict_types=1);

use App\Models\Sponsor;
use App\Models\SponsorClick;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveClickCountFromSponsors extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        // Allow SponsorClick to mass fill
        // Add clicks as sponsorclick element
        foreach (Sponsor::cursor() as $sponsor) {
            $click = new SponsorClick();
            $click->sponsor_id = $sponsor->id;
            $click->count = $sponsor->click_count;
            $click->save();
        }

        Schema::table('sponsors', static function (Blueprint $table) {
            $table->dropColumn('click_count');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('sponsors', static function (Blueprint $table) {
            $table->integer('click_count')->unsigned()->default(0)->comment('Number of clicks')->after('view_count');
        });
    }
}
