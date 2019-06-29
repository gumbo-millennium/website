<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNullableJoinSubmissionFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('join_submissions', function (Blueprint $table) {
            $table->string('first_name', 100)
                ->nullable()
                ->default(null)
                ->change();

            $table->text('phone')
                ->nullable()
                ->default(null)
                ->comment('Encrypted phone number')
                ->change();

            $table->text('date_of_birth')
                ->nullable()
                ->default(null)
                ->comment('Encrypted date of birth, as dd-mm-yyyy')
                ->change();

            $table->text('gender')
                ->nullable()
                ->default(null)
                ->comment('User supplied gender')
                ->change();

            $table->text('street')
                ->nullable()
                ->default(null)
                ->comment('Encrypted street name')
                ->change();

            $table->text('number')
                ->nullable()
                ->default(null)
                ->comment('Encrypted number')
                ->change();

            $table->text('city')
                ->nullable()
                ->default(null)
                ->comment('Encrypted city')
                ->change();

            $table->text('postal_code')
                ->nullable()
                ->default(null)
                ->comment('Encrypted zipcode')
                ->change();

            $table->text('country')
                ->nullable()
                ->default(null)
                ->comment('Encrypted country')
                ->change();

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
