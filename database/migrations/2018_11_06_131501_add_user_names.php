<?php

use App\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserNames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')
                ->default(null)
                ->nullable()
                ->after('id')
                ->comment('First name of the user.');
            $table->string('insert')
                ->default(null)
                ->nullable()
                ->after('name')
                ->comment('Insert of the user (tussenvoegsel).');

            $table->renameColumn('name', 'last_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('users', 'first_name')) {
            // Merge `first_name`, `insertion` and `last_name` into the `last_name` field
            User::lockForUpdate()->chunkById(100, function ($users) {
                foreach ($users as $user) {
                    $user->last_name = implode(' ', array_filter([
                        $users->get('first_name'),
                        $users->get('insert'),
                        $users->get('last_name')
                    ]));
                    $user->save();
                }
            });
        }

        // Remove first name and insertion
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('last_name', 'name');
            $table->dropColumn(['first_name', 'insert']);
        });
    }
}
