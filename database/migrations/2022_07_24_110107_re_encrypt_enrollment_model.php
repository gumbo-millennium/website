<?php

declare(strict_types=1);

use App\Models\Enrollment;
use App\Traits\DecryptsOldValues;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReEncryptEnrollmentModel extends Migration
{
    use DecryptsOldValues;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $foundModels = Enrollment::get()->lazy();
        $resetModels = [];
        $oneYearAgo = Date::now()->subYear();

        foreach ($foundModels as $model) {
            try {
                // Get the attribute, check for an exception
                $model->getAttribute('data');

                continue;
            } catch (DecryptException $exception) {
                Log::info('Got decryption exception on {title}', [
                    'title' => sprintf('[%s enrollment for %s]', $model->user?->name ?? '-', $model->activity?->name ?? '-'),
                ]);

                $resetModels[] = $model->id;

                if ($model->created_at > $oneYearAgo) {
                    Log::warning('Decryption exception is on {id}, less than a year old', [
                        'id' => $model->id,
                    ]);
                }
            }
        }

        DB::update('UPDATE enrollments SET data = null WHERE id IN (?)', [
            implode(', ', $resetModels),
        ]);
    }
}
