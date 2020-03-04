<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DropFileDownloadsTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('file_downloads');
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        // noop
    }
}
