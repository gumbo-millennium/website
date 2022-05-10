<?php

declare(strict_types=1);

namespace App\View\Components\Cards;

use App\Helpers\Str;
use App\Models\Page as PageModel;
use Closure;
use Illuminate\Support\Facades\View;
use Illuminate\View\Component;

class Page extends Component
{
    private PageModel $page;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(PageModel $page)
    {
        $this->page = $page;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return Closure|\Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        $page = $this->page;

        return View::make('components.card', [
            'href' => route('group.show', $page->only('group', 'slug')),
            'image' => $page->cover,
            'lead' => $page->category,
            'title' => $page->name,
            'description' => $page->description ?? Str::words(strip_tags($page->html ?? ''), 10),
        ]);
    }
}
