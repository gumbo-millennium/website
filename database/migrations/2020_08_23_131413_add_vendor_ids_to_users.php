<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVendorIdsToUsers extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        // Update schema
        Schema::table('users', static function (Blueprint $table) {
            $col = $table->json('vendor_ids')->after('conscribo_id');
            if (DB::getDriverName() === 'sqlite') {
                $col->default('[]');
            }
        });

        // Update users
        foreach (User::withTrashed()->cursor() as $user) {
            \assert($user instanceof User);
            $user
                ->setVendorId('stripe', $user->stripe_id)
                ->setVendorId('conscribo', $user->conscribo_id)
                ->save();
        }
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('users', static function (Blueprint $table) {
            $table->dropColumn('vendor_ids');
        });
    }
}
