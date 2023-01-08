<?php

declare(strict_types=1);

use App\Models\ActivityMessage;
use App\Models\Ticket;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add a many-to-many relationship between activity messages and tickets.
 */
class AddTicketsToActivityMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_message_ticket', function (Blueprint $table) {
            $table->foreignIdFor(ActivityMessage::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Ticket::class)->constrained()->cascadeOnDelete();

            $table->primary([
                'activity_message_id',
                'ticket_id',
            ], 'activity_message_ticket_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activity_message_ticket');
    }
}
