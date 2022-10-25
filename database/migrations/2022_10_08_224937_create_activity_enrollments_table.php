<?php

declare(strict_types=1);

use App\Models\States\Enrollment\Created;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityEnrollmentsTable extends Migration
{
    public function up(): void
    {
        Schema::create('activity_enrollments', function (Blueprint $table) {
            $table->id();
            $table->uuid('previous_id')->nullable()->unique();

            $table->foreignId('activity_id')->constrained();
            $table->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained();

            $table->string('state', 40)->default(Created::$name);

            $table->unsignedSmallInteger('price')->nullable();
            $table->unsignedSmallInteger('total_price')->nullable();

            $table->string('user_type', 10)->default('normal');
            $table->timestamp('expire')->nullable();

            $table->string('transfer_secret')->nullable();
            $table->string('ticket_code', 16)->nullable();
            $table->timestamp('consumed_at')->nullable();
            $table->foreignId('consumed_by_id')->nullable()->constrained('users')->nullOnDelete();

            $table->mediumText('data')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->string('deleted_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_enrollments');
    }
}
