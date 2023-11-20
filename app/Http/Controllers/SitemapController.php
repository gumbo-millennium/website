<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\NewsItem;
use App\Models\Page;
use App\Models\Sponsor;
use DOMDocument;
use Generator;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

/**
 * Generates sitemaps for activities,.
 */
class SitemapController extends Controller
{
    private const SKIPPED_PAGES = [
        'word-lid',
        'error-404',
    ];

    /**
     * Make sure the request has a valid type before sending it.
     */
    public function __construct(Request $request)
    {
        // Check for XML support
        abort_unless(
            $request->accepts('text/xml'),
            HttpResponse::HTTP_NOT_ACCEPTABLE,
            'You need to be able to understand XML sitemaps',
        );
    }

    /**
     * Present index sitemap on homepage.
     *
     * @return HttpResponse
     */
    public function index(Request $request)
    {
        // Reject if the user can't handle XML
        if (! $request->accepts('text/xml')) {
            return new NotAcceptableHttpException(
                'Sitemap is only available as XML, but you don\'t seem to want that.',
            );
        }

        // Return the cached sitemap
        $document = Cache::remember('sitemap.index', Date::now()->addHour(), fn () => $this->buildSitemap());

        return Response::make($document)
            ->header('Content-Type', 'text/xml; charset=utf-8')
            ->setCache(['public' => true, 'max_age' => Date::now()->addHour()->diffInSeconds()]);
    }

    private function buildSitemap(): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');

        $root = $document->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'urlset');
        $document->appendChild($root);

        // Add stylesheet in root folder
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsl', 'http://www.w3.org/1999/XSL/Transform');
        $root->setAttributeNS('http://www.w3.org/1999/XSL/Transform', 'xsl:stylesheet', url('/sitemap.xsl'));

        $pages = [
            ...$this->getSitemapPages(),
            ...$this->getActivities(),
            ...$this->getNewsItems(),
            ...$this->getSponsors(),
        ];

        foreach ($pages as $page) {
            if (! Arr::has($page, 'url')) {
                continue;
            }

            $pageNode = $document->createElement('url');
            $root->appendChild($pageNode);

            $pageNode->appendChild($document->createElement('loc', $page['url']));

            if ($priority = Arr::get($page, 'priority')) {
                $pageNode->appendChild($document->createElement('priority', $priority));
            }

            if ($lastMod = Arr::get($page, 'lastmod')) {
                $pageNode->appendChild($document->createElement('lastmod', $lastMod->toAtomString()));
            }

            if ($changeFrequency = Arr::get($page, 'changefreq')) {
                $pageNode->appendChild($document->createElement('changefreq', $changeFrequency));
            }
        }

        // Add stylesheet
        $xslInstruction = $document->createProcessingInstruction(
            'xml-stylesheet',
            'type="text/xsl" href="/sitemap.xsl"',
        );
        $document->insertBefore($xslInstruction, $root);

        return $document->saveXML(null, LIBXML_NOEMPTYTAG);
    }

    private function getSitemapPages(): Generator
    {
        yield [
            'url' => route('home'),
            'priority' => '1.0',
            'lastmod' => max([
                Date::parse(NewsItem::max('updated_at')),
                Date::parse(Activity::max('updated_at')),
                Date::parse(Page::max('updated_at')),
            ]),
            'changefreq' => 'hourly',
        ];

        foreach (Page::where('hidden', false)->get() as $model) {
            yield [
                'url' => $model->url,
                'priority' => '0.8',
                'lastmod' => $model->updated_at,
                'changefreq' => 'weekly',
            ];
        }
    }

    private function getActivities(): Generator
    {
        yield [
            'url' => route('activity.index'),
            'priority' => '0.9',
            'lastmod' => Date::parse(Activity::max('updated_at')),
            'changefreq' => 'daily',
        ];
        foreach (Activity::whereAvailable()->get() as $model) {
            yield [
                'url' => route('activity.show', $model),
                'priority' => '0.8',
                'lastmod' => $model->updated_at,
                'changefreq' => $model->end_date < Date::now() ? 'weekly' : 'daily',
            ];
        }
    }

    private function getNewsItems(): Generator
    {
        yield [
            'url' => route('news.index'),
            'priority' => '0.9',
            'lastmod' => Date::parse(NewsItem::max('updated_at')),
            'changefreq' => 'daily',
        ];
        foreach (NewsItem::whereAvailable()->get() as $model) {
            yield [
                'url' => route('news.show', $model),
                'priority' => '0.6',
                'lastmod' => $model->updated_at,
            ];
        }
    }

    private function getSponsors(): Generator
    {
        yield [
            'url' => route('sponsors.index'),
            'priority' => '0.6',
            'changefreq' => 'weekly',
        ];
        foreach (Sponsor::whereAvailable()->whereNotNull('contents_title')->get() as $model) {
            yield [
                'url' => route('sponsors.show', $model),
                'priority' => '0.5',
                'lastmod' => $model->updated_at,
            ];
        }
    }
}
