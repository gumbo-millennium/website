<?php

use App\Models\Activity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentTypeToActivities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->string('payment_type', 15)->nullable()->default(null)->after('enrollment_end');
        });

        Activity::where(function ($query) {
            $query->whereNotNull('price_member')
                ->orWhereNotNull('price_guest');
        })->update(['payment_type' => 'intent']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('payment_type');
        });
    }
}
