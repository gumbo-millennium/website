<?php

namespace App\Jobs;

use App\Models\Page;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CreateVersionedPagesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Get controller
        $pages = app()->call('App\Http\Controllers\PageController@getVersionedPages');
        if (!$pages) {
            return false;
        }

        // Create missing files
        foreach ($pages as $slug => $page) {
            // Get contents
            $entry = Page::updateOrCreate(
                ['slug' => $slug],
                ['type' => Page::TYPE_GIT]
            );

            $entry->created_at = $page['created_at'];
            $entry->updated_at = $page['updated_at'];
            $entry->contents = $page['content'];
            $entry->save(['created_at', 'updated_at', 'contents']);

            // Log stuff
            logger()->debug(
                $entry->wasRecentlyCreated ? 'Created {entry}.' : 'Updated {entry}.',
                compact('entry')
            );
        }

        // Mark existing files
        $existingPages = Page::whereType(Page::TYPE_GIT)->whereNotIn('slug', \array_keys($pages))->get();
        foreach ($existingPages as $page) {
            // Delete if empty
            if (empty($page->content)) {
                // Delete entry
                $page->delete();

                // Log
                logger()->debug('Deleted {page}.', compact('page'));
                continue;
            }

            // Change type if not empty
            $page->type = in_array($page->slug, Page::REQUIRED_PAGES) ? Page::TYPE_REQUIRED : Page::TYPE_USER;
            $page->save(['type']);

            // Log
            logger()->debug('Deleted {page}.', compact('page'));
        }
    }

    /**
     * Returns a title from a slug
     * @param string $slug
     * @return string
     */
    public function buildTitle(string $slug): string
    {
        $name = Str::afterLast($slug, '-slash-');
        $name = str_replace('-', ' ', $name);
        return Str::title(trim($name));
    }
}
