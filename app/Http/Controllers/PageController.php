<?php

namespace App\Http\Controllers;

use Advoor\NovaEditorJs\NovaEditorJs;
use App\Form;
use App\Models\Page;
use Corcel\Model\Post;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageController extends Controller
{
    /**
     * Views to use for types of contents
     */
    private const CLASS_VIEW_MAP = [
        Page::class => 'static.page',
        Form::class => 'static.form',
        Post::class => 'static.post',
    ];

    /**
     * Renders the homepage
     *
     * @return Response
     */
    public function homepage()
    {
        return $this->renderPage(Page::SLUG_HOMEPAGE);
    }

    /**
     * Renders the Privacy Policy
     *
     * @return Response
     */
    public function privacy()
    {
        return $this->renderPage(Page::SLUG_PRIVACY);
    }

    /**
     * Handles fallback routes
     *
     * @return Response
     */
    public function fallback(Request $request)
    {
        return $this->renderPage(trim($request->path(), '/\\'));
    }

    /**
     * Renders a single page, if possible
     *
     * @param string $slug
     * @return Response
     */
    protected function renderPage(string $slug)
    {
        $page = Page::whereSlug($slug)->first() ?? Page::whereSlug(Page::SLUG_404)->first();

        if (!$page) {
            throw new NotFoundHttpException();
        }

        return view(self::CLASS_VIEW_MAP[Page::class])->with([
            'page' => $page,
            'pageContents' => $this->getHtmlContents($page)
        ]);
    }

    /**
     * Returns the HTML contents of the page, or null if empty
     *
     * @param Page $page
     * @return string|null
     */
    protected function getHtmlContents(Page $page) : ?string
    {
        if (!$page->contents) {
            return null;
        }

        return NovaEditorJs::generateHtmlOutput($page->contents);
    }
}
