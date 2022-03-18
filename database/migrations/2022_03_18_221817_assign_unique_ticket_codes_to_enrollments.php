<?php

declare(strict_types=1);

use App\Facades\Enroll;
use App\Models\Enrollment;
use Illuminate\Database\Migrations\Migration;

class AssignUniqueTicketCodesToEnrollments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Enrollment::withoutGlobalScopes()->chunk(100, function ($tickets) {
            foreach ($tickets as $ticket) {
                Enroll::updateTicketCode($ticket);
                $ticket->save();
            }
        });
    }
}
