<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberReferralsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_referrals', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('subject');
            $table->string('referred_by');
            $table->unsignedBigInteger('user_id')->nullable()->default(null);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_referrals');
    }
}
