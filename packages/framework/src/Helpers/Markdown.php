<?php

namespace Hyde\Framework\Helpers;

use Hyde\Framework\Concerns\Markdown\HasMarkdownFeatures;

/**
 * General interface for Markdown services.
 *
 * @see \Hyde\Framework\Services\MarkdownConverterService
 */
class Markdown
{
    use HasMarkdownFeatures;
}
