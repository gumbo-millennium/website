<?php

declare(strict_types=1);

use App\Enums\Models\Minisite\PageType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('minisite_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('minisites')->cascadeOnDelete();
            $table->string('type', 10)->default(PageType::Default->value);

            $table->string('title');
            $table->string('slug');

            $table->boolean('visible')->default(1);

            $table->text('contents')->nullable();
            $table->string('cover')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by_id')->nullable()->constrained('users')->nullOnDelete();

            $table->unique(['site_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('minisite_pages');
    }
};
