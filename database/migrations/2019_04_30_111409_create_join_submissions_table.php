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
            $table->bigIncrements('id');
            $table->timestamps();

            // Names
            $table->string('first_name', 100);
            $table->string('insert', 100)->nullable()->default(null);
            $table->string('last_name', 100);

            // Contact info
            $table->string('email', 120)->index();
            $table->text('phone')->comment('Encrypted phone number');

            // User details
            $table->text('date_of_birth')->comment('Encrypted date of birth, as dd-mm-yyyy');
            $table->string('gender', 20)->comment('User supplied gender');

            // Contact data
            $table->text('street')->comment('Encrypted street name');
            $table->text('number')->comment('Encrypted number');
            $table->text('city')->comment('Encrypted city');
            $table->text('postal_code')->comment('Encrypted zipcode');
            $table->text('country')->comment('Encrypted country');

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
