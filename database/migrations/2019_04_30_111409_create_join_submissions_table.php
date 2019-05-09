<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJoinSubmissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('join_submissions', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            // Names
            $table->string('first_name', 100);
            $table->string('insert', 100)->nullable()->default(null);
            $table->string('last_name', 100);

            // Contact info
            $table->string('phone', 20)->comment('Encrypted phone number');
            $table->string('email', 120)->index();

            // User details
            $table->string('date_of_birth')->comment('Encrypted date of birth, as dd-mm-yyyy');
            $table->string('gender', 20)->comment('User supplied gender');

            // Contact data
            $table->string('street')->comment('Encrypted street name');
            $table->string('number')->comment('Encrypted number');
            $table->string('city')->comment('Encrypted city');
            $table->string('postal_code')->comment('Encrypted zipcode');
            $table->string('country')->comment('Encrypted country');

            // Result
            $table->boolean('granted')
                ->comment('Result of the reuqest, null if no result yet.')
                ->nullable()
                ->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('join_submissions');
    }
}
