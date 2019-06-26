<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddJoinSubmissionBooleans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('join_submissions', function (Blueprint $table) {
            $table->boolean('windesheim_student')
                ->default(null)
                ->nullable()
                ->after('country')
                ->comment('User studies at Windesheim');

            $table->boolean('newsletter')
                ->default(null)
                ->nullable()
                ->after('windesheim_student')
                ->comment('User wants the newsletter');
            //
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('join_submissions', function (Blueprint $table) {
            //
        });
    }
}
