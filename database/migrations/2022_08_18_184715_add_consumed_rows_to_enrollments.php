<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConsumedRowsToEnrollments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->after('payment_source', function (Blueprint $table) {
                $table->timestamp('consumed_at')->nullable();
                $table->foreignId('consumed_by_id')->nullable()->constrained('users')->nullOnDelete();
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn('consumed_at');
            $table->dropConstrainedForeignId('consumed_by_id');
        });
    }
}
