<?php

declare(strict_types=1);

namespace Hyde\Pages;

use Hyde\Pages\HybridPages\HybridPageCompiler;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;

class HybridPage extends MarkdownPage
{
    public static string $fileExtension = '.hmd';

    public function compile(): string
    {
        return View::make($this->getBladeView())->with([
            'title' => $this->title,
            'content' => new HtmlString(
                (new HybridPageCompiler($this))->handle(),
            ),
        ])->render();
    }
}
