<?php

declare(strict_types=1);

namespace Hyde\Markdown\Processing;

use Hyde\Hyde;
use Hyde\Framework\Exceptions\RouteNotFoundException;
use Hyde\Markdown\Contracts\MarkdownPostProcessorContract;

class DynamicMarkdownLinkProcessor implements MarkdownPostProcessorContract
{
    public static function postprocess(string $html): string
    {

        return $html;
    }
}
