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
        if ($newsArticle->author_id === null) {
            return;
        }

        $user = request()->user();
        if ($user) {
            $newsArticle->author_id = $user->id;
        }
    }
}
