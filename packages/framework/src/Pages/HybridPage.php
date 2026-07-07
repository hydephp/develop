<?php

namespace Hyde\Pages;

use Illuminate\Support\Facades\View;

class HybridPage extends MarkdownPage
{
    public static string $fileExtension = '.hmd';

    public function compile(): string
    {
        return View::make($this->getBladeView())->with([
            'title' => $this->title,
            'content' => $this->markdown->toHtml(static::class),
        ])->render();
    }
}
