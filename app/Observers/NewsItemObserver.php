<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\NewsItem;

class NewsItemObserver
{
    /**
     * Try to make sure a news article has an author.
     *
     * @return void
     */
    public function saving(NewsItem $newsArticle)
    {
        // Add user if missing
        if ($newsArticle->author_id !== null || auth()->check() === false) {
            return;
        }

        // Update author if available
        $newsArticle->author_id = auth()->user()->id;
    }
}
