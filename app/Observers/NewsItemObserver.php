<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\NewsItem;
use Carbon\CarbonInterface;
use Html2Text\Html2Text;
use Mtownsend\ReadTime\ReadTime;

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
            // Update author if available
            $newsArticle->author_id ??= $user->id;
        }

        // Determine text-only content
        $content = (string) $newsArticle->html;
        $textContent = (new Html2Text($content))->getText();

        // Determine read time
        $readTimeObject = new ReadTime($textContent, false);
        $readTimeObject->abbreviated(true);
        $readTimeObject->omitSeconds(false);
        $readTimeData = $readTimeObject->toArray();
        $readFinished = now()
            ->addMinutes($readTimeData['minutes'])
            ->addSeconds($readTimeData['seconds']);

        $readTotalTime = $readFinished->diffInSeconds(now());
        $readTime = $readFinished->diffForHumans([
            'syntax' => CarbonInterface::DIFF_ABSOLUTE,
            'options' => CarbonInterface::CEIL,
            'parts' => 1
        ]);

        // Assign read time
        $newsArticle->read_time = $readTotalTime ? $readTime : null;
    }
}
