<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            // Event meta
            $table->string('name');
            $table->string('slug', 60)->unique();
            $table->string('tagline', 150)->nullable()->default(null);
            $table->json('description')->nullable()->default(null);

            // Event dates
            $table->dateTimeTz('event_start')->comment('Start date and time');
            $table->dateTimeTz('event_end')->comment('End date and time');

            // Enrollment places
            $table->unsignedTinyInteger('seats')
                ->comment('Total number of seats')
                ->nullable()
                ->default(null);
            $table->unsignedTinyInteger('public_seats')
                ->comment('Number of seats for non-members (part of total seats count')
                ->nullable()
                ->default(null);

            // Event pricing
            $table->unsignedSmallInteger('price_member')
                ->comment('Price for Gumbo members')
                ->nullable()
                ->default(null);
            $table->unsignedSmallInteger('price_guest')
                ->comment('Price for Gumbo members')
                ->nullable()
                ->default(null);

            // Enroll dates
            $table->dateTimeTz('enrollment_start')
                ->comment('Start date and time for users to enroll')
                ->nullable()
                ->default(null);
            $table->dateTimeTz('enrollment_end')
                ->comment('End date and time for users to (un)enroll')
                ->nullable()
                ->default(null);

            // Enrollment questions (arbitrary data)
            $table->json('enrollment_questions')
                ->comment('Extra information asked when enrolling')
                ->nullable()
                ->default(null);

            // Add owning role
            $table->unsignedBigInteger('role_id')
                ->comment('ID of the owning role')
                ->nullable();

            // Add user link
            $table->foreign('role_id')
                ->references('id')->on(config('permission.table_names.roles'))
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activities');
    }
}
