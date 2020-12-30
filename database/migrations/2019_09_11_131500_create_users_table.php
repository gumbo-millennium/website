<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->softDeletes();

            // Name
            $table->string('first_name');
            $table->string('insert')->nullable()->default(null);
            $table->string('last_name');

            // E-mail data
            $table->string('email', 185)->unique();
            $table->timestamp('email_verified_at')->nullable();

            // Password and remember-me
            $table->string('password');
            $table->rememberToken();

            // Alias and gender
            $table->string('alias', 100)->nullable()->default(null);
            $table->string('gender', 80)->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
