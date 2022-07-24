<?php

declare(strict_types=1);

use App\Traits\DecryptsOldValues;
use Illuminate\Contracts\Encryption\DecryptException;
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
            try {
                DB::update('UPDATE users SET address = ? WHERE id = ?', [
                    Crypt::encrypt($this->decryptJsonValue($row->address)),
                    $row->id,
                ]);
                Log::info('Updated address on user {email}', ['email' => $row->email]);
            } catch (JsonException|DecryptException $exception) {
                Log::warning('Failed to update address on user {email}: {exception}', [
                    'email' => $row->email,
                    'exception' => $exception,
                ]);
            }
        }
    }
}
