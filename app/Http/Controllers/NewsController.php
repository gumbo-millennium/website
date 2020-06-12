<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\SponsorService;
use App\Models\NewsItem;
use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOTools;

/**
 * Renders user-generated news articles
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class NewsController extends Controller
{
    /**
     * Renders a news index, per 15 pages
     * @return Response
     */
    public function index()
    {
        // Get 15 items at a time, newest first
        $newsItemCount = NewsItem::whereAvailable()->count();
        $allNewsItems = NewsItem::whereAvailable()->paginate(15);

        // Set meta
        $title = 'Nieuws';
        $description = 'Het laatste nieuws van Gumbo Millennium, in één overzichtelijk overzicht';
        $url = route('news.index');
        $newsItems = [];

        foreach ($allNewsItems as $index => $item) {
            $url = route('news.show', ['news' => $item]);

            $newItem = [
                '@type' => 'NewsArticle',
                '@id' => $url,
                'url' => $url,
                'name' => $item->title,
                'description' => $item->headline,
                'position' => $index + 1,
                'author' => $item->sponsor || optional($item->author)->name,
                'datePublished' => $item->published_at,
                'headline' => $item->headline,
            ];

            if ($item->sponsor) {
                $newItem['sponsor'] = $item->sponsor;
            }

            $newsItems[] = $newItem;
        }

        // Set SEO
        SEOTools::setTitle($title);
        SEOTools::setDescription($description);
        SEOTools::setCanonical($url);

        // Set Open Graph
        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl($url);

        // Set JSON+LD
        JsonLd::setType('ItemList');
        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::setUrl($url);
        JsonLd::addValue('numberOfItems', $newsItemCount);
        JsonLd::addValue('itemListElement', $newsItems);

        // Return the view with all items
        return view('news.index')->with([
            'items' => $allNewsItems
        ]);
    }

    /**
     * Renders a single item
     * @param NewsItem $item
     * @return Response
     */
    public function show(NewsItem $item)
    {
        // meta
        $title = $item->title;
        $description = $item->summary;
        $url = route('news.show', ['news' => $item]);

        // Set SEO
        SEOTools::setTitle($title);
        SEOTools::setDescription($description);
        SEOTools::setCanonical($url);
        SEOTools::addImages([
            $item->image->url('social')
        ]);

        // Set Open Graph
        OpenGraph::setUrl($url);

        // Set JSON+LD
        JsonLd::setType('NewsArticle');
        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::setUrl($url);
        JsonLd::addValue('@id', $url);
        JsonLd::addValue('author', $item->sponsor || optional($item->author)->name);
        JsonLd::addValue('datePublished', $item->published_at);
        JsonLd::addValue('headline', $item->headline);

        // Add JSON sponsor and hide the ad if sponsored
        if ($item->sponsor) {
            JsonLd::addValue('sponsor', $item->sponsor);
            app(SponsorService::class)->hideSponsor();
        }

        // Show item
        return view('news.show')->with(compact('item'));
    }
}
