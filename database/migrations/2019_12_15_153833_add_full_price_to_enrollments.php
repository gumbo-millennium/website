<?php

use App\Models\Enrollment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFullPriceToEnrollments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->unsignedSmallInteger('total_price')->nullable()->default(null)->after('price');
        });

        foreach (Enrollment::whereNotNull('price')->cursor() as $enrollment) {
            $enrollment->total_price = max($enrollment->price, $enrollment->price + config('gumbo.transfer-fee', 0));
            $enrollment->save(['total_price']);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn('total_price');
        });
    }
}
