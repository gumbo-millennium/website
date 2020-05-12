<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSponsorClicksTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('sponsor_clicks', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sponsor_id');
            $table->unsignedSmallInteger('count')->default(1);
            $table->date('date');

            // Couple sponsor_click to sponsor, deleting if removed
            $table
                ->foreign('sponsor_id')
                ->references('id')
                ->on('sponsors')
                ->onDelete('cascade');

            // Allow only one sponsor click listing per day
            $table->unique(['sponsor_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sponsor_clicks');
    }
}
