<?php

declare(strict_types=1);

namespace App\Console\Commands\Dev;

use App\Models;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ResetMissingImages extends Command
{
    private const MODELS = [
        Models\Activity::class => ['poster'],
        Models\NewsItem::class => ['cover'],
        Models\Page::class => ['cover'],
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'CMD'
        dev:reset-images
            {--dry-run : Don't actually write the data}
        CMD;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resets all resources that have missing images to their default image.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::beginTransaction();
        $disk = Storage::disk(Config::get('gumbo.images.disk'));

        foreach (self::MODELS as $class => $fields) {
            $resetCount = 0;
            foreach ($fields as $field) {
                foreach ($class::query()->whereNotNull($field)->lazy() as $model) {
                    if ($disk->missing($model->{$field})) {
                        $resetCount++;
                        $model->{$field} = null;
                        $model->save();
                    }
                }
            }

            $this->line(sprintf('Will reset <info>%d</> paths on <info>%s</>', $resetCount, class_basename($class)));
        }

        if ($this->option('dry-run')) {
            $this->comment('Dry run, not committing changes.');
            DB::rollBack();
        } else {
            $this->line('Committing changes...');
            DB::commit();
        }

        return 0;
    }
}
