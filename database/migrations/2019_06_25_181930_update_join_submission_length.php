<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Changes length of submmissin items
 */
class UpdateJoinSubmissionLength extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('join_submissions', function (Blueprint $table) {
            $table->text('phone')->comment('Encrypted phone number')->change();
            $table->text('date_of_birth')->comment('Encrypted date of birth, as dd-mm-yyyy')->change();
            $table->text('gender')->comment('User supplied gender')->change();
            $table->text('street')->comment('Encrypted street name')->change();
            $table->text('number')->comment('Encrypted number')->change();
            $table->text('city')->comment('Encrypted city')->change();
            $table->text('postal_code')->comment('Encrypted zipcode')->change();
            $table->text('country')->comment('Encrypted country')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No undo
    }
}
