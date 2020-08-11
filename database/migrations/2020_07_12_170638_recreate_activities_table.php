<?php

declare(strict_types=1);

use App\Models\Activity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RecreateActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        // noop if exists
        if (Schema::hasTable('activities')) {
            return;
        }

        // Create table
        Schema::create('activities', static function (Blueprint $table) {
            $table->bigIncrements('id');

            // Dates
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('cancelled_at')->nullable()->default(null);

            // Basic meta
            $table->string('name');
            $table->string('slug', 60)->unique();
            $table->string('tagline', 150)->nullable()->default(null);
            $table->json('description')->nullable()->default(null);
            $table->string('statement', 22)->nullable()->default(null);

            // Location
            $table->string('location', 64)->nullable()->default(null);
            $table->string('location_address', 255)->nullable()->default(null);
            $table->string('location_type', 20)->default(Activity::LOCATION_OFFLINE);

            // Event dates
            $table->dateTimeTz('start_date')->comment('Start date and time');
            $table->dateTimeTz('end_date')->comment('End date and time');

            // Enroll dates
            $table->dateTimeTz('enrollment_start')->nullable()->default(null);
            $table->dateTimeTz('enrollment_end')->nullable()->default(null);

            // Enrollment info
            $table->boolean('is_public')->default('1');
            $table->unsignedTinyInteger('seats')->nullable()->default(null);
            $table->json('enrollment_questions')->nullable()->default(null);

            // Event pricing
            $table->smallInteger('price')->unsigned()->nullable()->default(null);
            $table->string('payment_type', 15)->nullable()->default(null);
            $table->smallInteger('member_discount')->unsigned()->nullable()->default(null);
            $table->tinyInteger('discount_count')->unsigned()->nullable()->default(null);
            $table->string('stripe_coupon_id')->nullable()->default(null);

            // Cancellation and moves
            $table->text('cancelled_reason')->nullable()->default(null);
            $table->timestamp('rescheduled_from')->nullable()->default(null);
            $table->string('rescheduled_reason')->nullable()->default(null);
            $table->timestamp('postponed_at')->nullable()->default(null);
            $table->string('postponed_reason')->nullable()->default(null);

            // Artwork
            $table->string('image')->nullable()->default(null);

            // Add owning role and user
            $table->unsignedBigInteger('role_id')->nullable()->default(null);
            $roleTable = config('permission.table_names.roles');
            $table->foreign('role_id')->references('id')->on($roleTable)->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activities');
    }
}
