<?php

namespace App\Http\Controllers;

use App\Models\NewsItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Renders user-generated news articles 
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class NewsController extends Controller
{
    /**
     * Renders a news index, per 15 pages
     *
     * @return Response
     */
    public function index()
    {
        // Get 15 items at a time, newest first
        $allNewsItems = NewsItem::orderByDesc('created_at')->paginate(15);

        // Return the view with all items
        return view('news.index')->with([
            'items' => $allNewsItems
        ]);
    }

    /**
     * Renders a single item
     *
     * @param NewsItem $item
     * @return Response
     */
    public function show(NewsItem $item)
    {
        return view('news.show')->with([
            'item' => $item
        ]);
    }
}
