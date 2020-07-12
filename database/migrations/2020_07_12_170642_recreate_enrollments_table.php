<?php

declare(strict_types=1);

use App\Models\States\Enrollment\Created;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RecreateEnrollmentsTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        // noop if exists
        if (Schema::hasTable('enrollments')) {
            return;
        }

        // Create table
        Schema::create('enrollments', static function (Blueprint $table) {
            // IDs and owners
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('activity_id');

            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            $table->dateTime('expire')->nullable()->default(null);

            // State
            $table->string('state', 64)->default(str_replace('\\', '\\\\', Created::class));

            // Price data
            $table->smallInteger('price')->unsigned()->nullable()->default(null);
            $table->smallInteger('total_price')->unsigned()->nullable()->default(null);
            $table->string('payment_intent')->nullable()->default(null)->unique();
            $table->string('payment_source')->nullable()->default(null);
            $table->string('payment_invoice')->nullable()->default(null);

            // Add system-data and user-supplied data
            $table->mediumText('data')->nullable()->default(null);
            $table->mediumText('form')->nullable()->default(null);

            // Transfers
            $table->string('transfer_secret')->nullable()->default(null)->unique();

            // Add extra flags
            $table->string('user_type', 6)->default('normal');
            $table->string('deleted_reason', 30)->nullable()->default(null);

            // Add user link
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('enrollments');
    }
}
