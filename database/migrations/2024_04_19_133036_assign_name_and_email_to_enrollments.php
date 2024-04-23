<?php

declare(strict_types=1);

use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $users = User::withoutGlobalScopes()
            ->get(['id', 'email', 'name'])
            ->keyBy('id');

        DB::transaction(function () use ($users) {
            /** @var User $user */
            foreach ($users as $user) {
                if (empty($user->name)) {
                    continue;
                }

                DB::update('UPDATE activity_enrollments SET name = ?, email = ? WHERE user_id = ?', [
                    $user->name,
                    $user->email,
                    $user->id,
                ]);
            }

            DB::update('UPDATE activity_enrollments SET name = ?, email = ? WHERE user_id IS NULL', [
                Enrollment::OWNER_NAME_DEFAULT,
                Enrollment::OWNER_EMAIL_DEFAULT,
            ]);
        });
    }
};
