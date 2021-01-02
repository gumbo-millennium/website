<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJoinSubmissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('join_submissions', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            // Names
            $table->string('first_name', 100)->nullable()->default(null);
            $table->string('insert', 100)->nullable()->default(null);
            $table->string('last_name', 100);

            // Contact info
            $table->string('email', 120)->index();
            $table->text('phone')->comment('Encrypted phone number')->nullable();

            // User details
            $table->text('date_of_birth')->comment('Encrypted date of birth, as dd-mm-yyyy')->nullable();
            $table->text('gender')->comment('User supplied gender')->nullable();

            // Contact data
            $table->text('street')->comment('Encrypted street name')->nullable();
            $table->text('number')->comment('Encrypted number')->nullable();
            $table->text('city')->comment('Encrypted city')->nullable();
            $table->text('postal_code')->comment('Encrypted zipcode')->nullable();
            $table->text('country')->comment('Encrypted country')->nullable();

            // User settings
            $table->boolean('windesheim_student')->default(0);
            $table->boolean('newsletter')->default(0);

            // Result
            $table->boolean('granted')->nullable()->default(null);
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
