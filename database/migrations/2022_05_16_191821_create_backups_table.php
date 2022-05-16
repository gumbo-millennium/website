<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBackupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('previous_backup_id')
                ->nullable()
                ->constrained('backups')
                ->nullOnDelete();

            $table->string('type', 20);
            $table->string('disk');
            $table->string('path');

            $table->string('failed_reason')->nullable();

            $table->timestamps();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('backups');
    }
}
