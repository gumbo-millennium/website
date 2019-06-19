<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnrollmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            // Who's enrollment is it anyway?
            $table->unsignedBigInteger('user_id')
                ->comment('Owning user');

            // Who's enrollment is it anyway?
            $table->unsignedBigInteger('activity_id')
                ->comment('Owning activity');

            // Add enrollment data
            $table->mediumText('data')
                ->comment('Encrypted enrollment data')
                ->nullable()
                ->default(null);

            // Add user link
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            // Add activity link
            $table->foreign('activity_id')
                ->references('id')->on('activities')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('enrollments');
    }
}
