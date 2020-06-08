<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\NewsItem;

class NewsItemObserver
{
    /**
     * Try to make sure a news article has an author
     * @param NewsItem $newsArticle
     * @return void
     */
    public function saving(NewsItem $newsArticle)
    {
        // Add user if missing
        if ($newsArticle->author_id === null) {
            $user = request()->user();
            if ($user) {
                // Update author if available
                $newsArticle->author_id = $user->id;
            }
        }
    }
}
