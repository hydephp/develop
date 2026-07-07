<?php

declare(strict_types=1);

namespace Hyde\Pages;

use Illuminate\Support\Facades\View;
use Hyde\Framework\Actions\HybridPageCompiler;

class HybridPage extends MarkdownPage
{
    public static string $fileExtension = '.hmd';

    public function compile(): string
    {
        return View::make($this->getBladeView())->with([
            'title' => $this->title,
            'content' => (new HybridPageCompiler())->handle($this),
        ])->render();
    }
}
