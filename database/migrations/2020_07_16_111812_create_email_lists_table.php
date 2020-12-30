<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_lists', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('email')->unique();
            $table->string('service_id');
            $table->string('name')->nullable();

            $table->json('aliases');
            $table->json('members');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_lists');
    }
}
