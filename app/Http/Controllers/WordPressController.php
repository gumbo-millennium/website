<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Page;
use App\Form;
use App\Post;
use App\Activity;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WordPressController extends Controller
{
    const CLASS_VIEW_MAP = [
        Page::class => 'wordpress.page',
        Form::class => 'wordpress.form',
        Activity::class => 'wordpress.activity',
        Post::class => 'wordpress.post',
    ];

    /**
     * Renders the homepage
     *
     * @return Response
     */
    public function homepage()
    {
        return view(self::CLASS_VIEW_MAP[Page::class])->with(['page' => Page::homepage()]);
    }

    /**
     * Handles fallback routes
     *
     * @return Response
     */
    public function fallback(Request $request)
    {
        $slug = trim($request->path(), '/\\');

        $page = Page::slug($slug)->first();
        if (!$page) {
            $page = Page::slug('404')->first();
        }

        if (!$page) {
            throw new NotFoundHttpException;
        }

        return view('wordpress.page')->with(['page' => $page]);
    }
}
