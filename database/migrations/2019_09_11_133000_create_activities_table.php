<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activities', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('cancelled_at')->nullable()->default(null);

            // Event meta
            $table->string('name');
            $table->string('slug', 60)->unique();
            $table->string('tagline', 150)->nullable()->default(null);
            $table->json('description')->nullable()->default(null);

            // Event dates
            $table->dateTimeTz('start_date')->comment('Start date and time');
            $table->dateTimeTz('end_date')->comment('End date and time');

            // Enrollment places
            $table->unsignedTinyInteger('seats')->nullable()->default(null);
            $table->unsignedTinyInteger('public_seats')->nullable()->default(null);

            // Event pricing
            $table->unsignedSmallInteger('price_member')->nullable()->default(null);
            $table->unsignedSmallInteger('price_guest')->nullable()->default(null);

            // Enroll dates
            $table->dateTimeTz('enrollment_start')->nullable()->default(null);
            $table->dateTimeTz('enrollment_end')->nullable()->default(null);

            // Enrollment questions (arbitrary data)
            $table->json('enrollment_questions')->nullable()->default(null);

            // Add owning role and user
            $table->unsignedBigInteger('role_id')->nullable()->default(null);
            $table->unsignedBigInteger('user_id')->nullable()->default(null);

            // Add role and user foreign key
            $roleTable = config('permission.table_names.roles');
            $table->foreign('role_id')->references('id')->on($roleTable)->onDelete('set null');
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
