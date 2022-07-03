<?php

namespace Hyde\Framework\Models\Routing;

use Hyde\Framework\Contracts\PageContract;

interface RouteContract
{
    public function getSourceModel(): PageContract;

    public function getSourceFilePath(): string;

    public function getOutputFilePath(): string;
}
