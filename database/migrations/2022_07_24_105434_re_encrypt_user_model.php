<?php

declare(strict_types=1);

use App\Traits\DecryptsOldValues;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReEncryptUserModel extends Migration
{
    use DecryptsOldValues;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $query = DB::select('SElECT id, address, email FROM users WHERE address IS NOT NULL');

        foreach ($query as $row) {
            $newValue = null;

            try {
                $newValue = Crypt::encrypt($this->decryptJsonValue($row->address));
                Log::info('Decrypted address on user {email}', ['email' => $row->email]);
            } catch (InvalidArgumentException $exception) {
                Log::warning('Failed to decrypt address on user {email}: {exception}', [
                    'email' => $row->email,
                    'exception' => $exception,
                ]);
            }

            DB::update('UPDATE `users` SET `address` = ? WHERE `id` = ?', [$newValue, $row->id]);
        }
    }
}
