<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RecreateSponsorClicksTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        // noop if exists
        if (Schema::hasTable('sponsor_clicks')) {
            return;
        }

        // Create table
        Schema::create('sponsor_clicks', static function (Blueprint $table) {
            // ID and sponsor
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sponsor_id');

            // Count for the given date
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
