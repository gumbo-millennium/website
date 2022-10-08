<?php

declare(strict_types=1);

use App\Models\Enrollment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CopyEnrollmentsToActivityEnrollmentsTable extends Migration
{
    /**
     * Colums present on both the `enrollments` and `activity_enrollments` tables.
     */
    private const OVERLAPPING_COLUMNS = [
        'user_id',
        'activity_id',
        'ticket_id',
        'created_at',
        'updated_at',
        'deleted_at',
        'deleted_reason',
        'state',
        'price',
        'total_price',
        'user_type',
        'expire',
        'transfer_secret',
        'ticket_code',
        'data',
    ];

    public function up(): void
    {
        // Find all enrollments that haven't been copied over.
        $existingEnrollmentQuery = DB::table('enrollments')
            ->select([
                'id',
                ...self::OVERLAPPING_COLUMNS,
            ])
            ->whereNotIn('id', fn ($query) => $query->from('activity_enrollments')->select('previous_id'))
            ->orderBy('created_at')
            ->orderBy('updated_at')
            ->orderBy('id');

        // Create entries in table
        DB::table('activity_enrollments')->insertUsing([
            'previous_id',
            ...self::OVERLAPPING_COLUMNS,
        ], $existingEnrollmentQuery);

        // Update payments table
        $oldIdNewIdMap = DB::table('activity_enrollments')->get([
            'id',
            'previous_id',
        ])->only('previous_id', 'id')->toArray();

        // Run query for each created enrollment
        foreach ($oldIdNewIdMap as $data) {
            DB::table('payments')->where([
                'payable_type' => Enrollment::class,
                'payable_id' => $data->id,
            ])->update([
                'payable_id' => $data->previous_id,
            ]);
        }
    }
}
