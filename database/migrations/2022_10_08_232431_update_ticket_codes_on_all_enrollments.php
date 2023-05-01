<?php

declare(strict_types=1);

use App\Facades\Enroll;
use App\Models\Enrollment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Date;

class UpdateTicketCodesOnAllEnrollments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $query = Enrollment::query()
            ->whereHas('activity', fn ($query) => $query->where('end_date', '>', Date::now()))
            ->active();

        /** @var Enrollment $enrollment */
        foreach ($query->lazy(100) as $enrollment) {
            Enroll::updateBarcode($enrollment);
        }
    }
}
