<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransferSecretToEnrollments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enrollments', static function (Blueprint $table) {
            $table->string('transfer_secret')
                ->nullable()
                ->default(null)
                ->unique()
                ->after('expire');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enrollments', static function (Blueprint $table) {
            $table->dropColumn('transfer_secret');
        });
    }
}
