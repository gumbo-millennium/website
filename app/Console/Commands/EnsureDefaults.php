<?php

namespace App\Console\Commands;

use App\File;
use App\FileCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Jobs\UpdateWordPressTokenJob;

class EnsureDefaults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ensure-defaults';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Makes sure there\'s a default for some elements';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Handle default categories
        DB::beginTransaction();
        $this->handleFileCategories();
        DB::commit();

        // Handle files that started floating
        $this->handleFloatingFiles();

        // Handle WordPress authentication tokens
        $this->handleWpAuth();
    }

    public function handleFileCategories() : void
    {
        $action = null;
        $defaultCategories = FileCategory::where(['default' => true])->get();
        $categoryCount = $defaultCategories->count();

        if ($categoryCount === 1) {
            $defaultCategory = $defaultCategories->first();
            $this->line(sprintf(
                'Default category preserved as <info>%s</>.',
                $defaultCategory->title
            ));
            return;
        }

        if ($categoryCount == 0) {
            // Handle creation
            $defaultCategory = FileCategory::make([
                'default' => true,
                'title' => 'Overige'
            ]);
            $defaultCategory->save();
            $this->line(sprintf(
                'Default category created as <info>%s</>.',
                $defaultCategory->title
            ));
            return;
        }

        $options = $defaultCategories->pluck('slug')->toArray();
        $this->line('Multiple defaults exist!');
        $choice = $this->choice('Which option will be the new default?', $options, 0);

        $defaultCategory = FileCategory::findBySlugOrFail($choice);
        if (!$defaultCategory) {
            $this->error("Failed to pick category");
            abort();
        }

        foreach ($defaultCategories as $cat) {
            if (!$defaultCategory->is($cat)) {
                $cat->default = false;
                $cat->save();

                $this->line(sprintf(
                    'Removed default flag from <comment>%s</>.',
                    $cat->title
                ));
            }
        }

        $this->line(sprintf(
            'Default category is now <info>%s</>.',
            $defaultCategory->title
        ));
    }

    /**
     * Moves files without a category to the default one
     *
     * @return void
     */
    public function handleFloatingFiles() : void
    {
        // Get all floating files
        $files = File::doesntHave('categories')->get();
        $defaultCategory = FileCategory::findDefaultOrFail();

        DB::beginTransaction();
        foreach ($files as $file) {
            $this->line(sprintf(
                'Linking <info>%s</> to category <comment>%s</>.',
                $file->display_title,
                $defaultCategory->name
            ));

            $file->categories()->attach($defaultCategory);
        }
        DB::commit();
    }

    /**
     * Fires WordPress auth job
     *
     * @return void
     */
    protected function handleWpAuth() : void
    {
        dispatch(new UpdateWordPressTokenJob());
    }
}
