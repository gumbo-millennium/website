<?php

declare(strict_types=1);

use App\Models\Ticket;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReplaceMembersOnlyWithIsPublicOnTickets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->boolean('is_public')->default(1)->after('members_only');
        });

        Ticket::query()
            ->withoutGlobalScopes()
            ->where('members_only', true)
            ->update([
                'is_public' => false,
            ]);

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('members_only');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->boolean('members_only')->default(0)->after('is_public');
        });

        Ticket::query()
            ->withoutGlobalScopes()
            ->where('is_public', false)
            ->update([
                'members_only' => true,
            ]);

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('is_public');
        });
    }
}
