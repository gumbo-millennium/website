<?php

declare(strict_types=1);

use App\Traits\DecryptsOldValues;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class ReEncryptJoinSubmissionModel extends Migration
{
    use DecryptsOldValues;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $query = DB::select('SElECT id, phone, date_of_birth, street, number, city, postal_code, country FROM join_submissions');
        foreach ($query as $row) {
            try {
                $dateOfBirth = Date::parse($this->decryptValue($row->date_of_birth));

                DB::update(<<<'SQL'
                    UPDATE
                        `join_submissions`
                    SET
                        `phone` = ?,
                        `date_of_birth` = ?,
                        `street` = ?,
                        `number` = ?,
                        `city` = ?,
                        `postal_code` = ?,
                        `country` = ?
                    WHERE
                        `id` = ?
                SQL, [
                    Crypt::encryptString($this->decryptValue($row->phone)),
                    Crypt::encryptString($dateOfBirth->format('Y-m-d')),
                    Crypt::encryptString($this->decryptValue($row->street)),
                    Crypt::encryptString($this->decryptValue($row->number)),
                    Crypt::encryptString($this->decryptValue($row->city)),
                    Crypt::encryptString($this->decryptValue($row->postal_code)),
                    Crypt::encryptString($this->decryptValue($row->country)),
                    $row->id,
                ]);

                Log::info('Updated fields on join submission {id}', ['id' => $row->id]);
            } catch (InvalidArgumentException $exception) {
                Log::warning('Failed to update fields on join submission {id}: {exception}', [
                    'id' => $row->id,
                    'exception' => $exception,
                ]);
            }
        }
    }
}
