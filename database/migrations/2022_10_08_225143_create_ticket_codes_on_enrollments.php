<?php

declare(strict_types=1);

use App\Facades\Enroll;
use App\Models\Enrollment;
use Illuminate\Database\Migrations\Migration;

class CreateTicketCodesOnEnrollments extends Migration
{
    public function up(): void
    {
        // Update ticket codes for each enrollment
        foreach (Enrollment::withoutGlobalScopes()->lazy(100) as $enrollment) {
            if ($enrollment->ticket_code !== null) {
                continue;
            }

            Enroll::updateBarcode($enrollment);
            $enrollment->save();
        }
    }
}
