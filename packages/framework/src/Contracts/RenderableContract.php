<?php

namespace Hyde\Framework\Contracts;

use Illuminate\Contracts\View\View;

interface RenderableContract
{
    public static function getViewKey(): string;
    public function getSourceFilePath(): string;
    public function getOutputFilePath(): string;
}
