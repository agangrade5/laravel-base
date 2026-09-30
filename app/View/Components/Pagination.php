<?php

namespace App\View\Components;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\UrlWindow;
use Illuminate\View\Component;
use Illuminate\View\View;

class Pagination extends Component
{
    public array $elements;

    /**
     * Create a new component instance.
     *
     * @param LengthAwarePaginator $paginator
     * @param int $onEachSide
     *
     * @return void
     */
    public function __construct(
        public LengthAwarePaginator $paginator,
        public int $onEachSide = 1
    ) {
        $this->elements = UrlWindow::make(
            $paginator->onEachSide($onEachSide)
        );
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.pagination');
    }
}
