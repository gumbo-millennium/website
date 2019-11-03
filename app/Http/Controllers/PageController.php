<?php

namespace App\Http\Controllers;

use Advoor\NovaEditorJs\NovaEditorJs;
use App\Models\Page;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageController extends Controller
{
    /**
     * Renders the homepage
     *
     * @return Response
     */
    public function homepage()
    {
        return view('content.home');
        // return $this->render(Page::SLUG_HOMEPAGE);
    }

    /**
     * Renders the Privacy Policy
     *
     * @return Response
     */
    public function privacy()
    {
        return $this->render(Page::SLUG_PRIVACY);
    }

    /**
     * Handles fallback routes
     *
     * @return Response
     */
    public function fallback(Request $request)
    {
        return $this->render(trim($request->path(), '/\\'));
    }

    /**
     * Renders a single page, if possible
     *
     * @param string $slug
     * @return Response
     */
    protected function render(string $slug)
    {
        $page = Page::whereSlug($slug)->first() ?? Page::whereSlug(Page::SLUG_404)->first();

        if (!$page) {
            throw new NotFoundHttpException();
        }

        return view('content.page')->with([
            'page' => $page
        ]);
    }
}
