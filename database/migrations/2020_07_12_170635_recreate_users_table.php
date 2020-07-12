<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RecreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        // noop if exists
        if (Schema::hasTable('users')) {
            return;
        }

        Schema::create('users', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->softDeletes();

            // API ids
            $table->string('stripe_id', 255)->nullable()->default(null);
            $table->smallInteger('conscribo_id')->nullable()->default(null)->unique();

            // Name fields
            $table->string('first_name');
            $table->string('insert')->nullable()->default(null);
            $table->string('last_name');

            // Generated name (not on SQLite)
            $nameCol = $table->string('name')
                ->virtualAs('CONCAT_WS(" ", `first_name`, `insert`, `last_name`)');
            if (DB::getDriverName() === 'sqlite') {
                $nameCol->default('NO_SUPPORT');
            }

            // E-mail data
            $table->string('email', 185)->unique();
            $table->timestamp('email_verified_at')->nullable();

            // Password and remember-me
            $table->string('password');
            $table->rememberToken();

            // Alias and gender
            $table->string('alias', 100)->nullable()->default(null);
            $table->string('gender', 80)->nullable()->default(null);

            // Address and contact data (for payments)
            $table->text('address')->nullable()->default(null);
            $table->text('phone')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
