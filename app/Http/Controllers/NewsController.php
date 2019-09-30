<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Renders WordPress posts, which are news items in our vocbulary
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class NewsController extends Controller
{
    /**
     * Renders all news articles on the website.
     *
     * @return Response
     */
    public function index()
    {
        // Get 15 posts at a time
        $allPosts = Post::published()->paginate(15);

        // Return the view with all posts
        return view('main.news.list')->with([
            'posts' => $allPosts
        ]);
    }

    /**
     * Renders a single post
     *
     * @param Post $post
     * @return Response
     */
    public function post(Post $post)
    {
        return view('main.news.single')->with([
            'post' => $post
        ]);
    }
}
