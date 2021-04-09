<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFileExportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_exports', static function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('urlkey')->unique();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('path');
            $table->string('filename');

            $table->timestamps();
            $table->timestamp('expires_at')->nullable();

            $table->foreign('owner_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_exports');
    }
}
