<?php

declare(strict_types=1);

use App\Enums\Models\GoogleWallet\ReviewStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoogleWalletEventClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('google_wallet_event_classes', function (Blueprint $table) {
            $table->id();
            $table->string('wallet_id')->unique();
            $table->morphs('subject');

            $table->string('review_status', 20)->default(ReviewStatus::Unspecficied->value);
            $table->json('review')->nullable();

            $table->string('name');
            $table->string('location_name')->nullable();
            $table->string('location_address')->nullable();

            $table->timestamp('start_time');
            $table->timestamp('end_time');

            $table->string('uri')->nullable();
            $table->string('hero_image')->nullable();

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
        Schema::dropIfExists('google_wallet_event_classes');
    }
}
