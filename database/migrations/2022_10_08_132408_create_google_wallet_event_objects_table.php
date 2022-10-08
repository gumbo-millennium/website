<?php

declare(strict_types=1);

use App\Enums\Models\GoogleWallet\ObjectState;
use App\Enums\Models\GoogleWallet\ReviewStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoogleWalletEventObjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('google_wallet_event_objects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('google_wallet_event_classes')->restrictOnDelete();
            $table->string('wallet_id')->unique();
            $table->morphs('subject');
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('state', 10)->default(ObjectState::Unspecified->value);

            $table->string('review_status', 20)->default(ReviewStatus::Unspecficied->value);
            $table->json('review')->nullable();

            $table->unsignedSmallInteger('value')->nullable(); // Max value of € 665,35
            $table->string('ticket_number');
            $table->string('ticket_type');
            $table->string('barcode');

            $table->unsignedTinyInteger('installs')->default(0);
            $table->unsignedTinyInteger('removals')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('google_wallet_event_objects');
    }
}
