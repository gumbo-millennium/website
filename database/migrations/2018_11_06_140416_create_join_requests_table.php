<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJoinRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('join_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();

            // Address data
            $table->string('street', 100)->nullable()->default(null);
            $table->string('number', 10)->nullable()->default(null);
            $table->string('zipcode', 30)->nullable()->default(null);
            $table->string('city', 10)->nullable()->default(null);
            $table->string('country', 40)->nullable()->default(null);

            // Contact data
            $table->string('phone', 30)->nullable()->default(null);
            $table->date('date-of-birth')->nullable()->default(null);

            // Register acceptance of Privacy Policy
            $table->boolean('accept-policy')->default(false);
            $table->boolean('accept-newsletter')->default(false);

            // Register status
            $table->string('status', 10)->default('pending')->comment('Status of the request');
            $table->string('status_reason')->nullable()->default(null)->comment('Reason why this object has this status');
            $table->integer('status_provider_id')->unsigned()->nullable()->default(null)->comment('User who made the last status change');

            // Association
            $table->integer('user_id')->unsigned()->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('join_requests');
    }
}
