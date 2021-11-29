<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('activity_id')->nullable();

            $table->string('title');
            $table->text('description')->nullable();

            $table->unsignedSmallInteger('price')->nullable();
            $table->unsignedSmallInteger('quantity')->nullable();

            $table->boolean('members_only')->default(0);

            $table->timestamp('available_from')->nullable();
            $table->timestamp('available_until')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickets');
    }
}
