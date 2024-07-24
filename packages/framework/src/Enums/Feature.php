<?php

declare(strict_types=1);

namespace Hyde\Enums;

use function constant;

/**
 * A configurable feature that belongs to the Features class.
 *
 * @see \Hyde\Facades\Features
 */
enum Feature
{
    // Page Modules
    case HtmlPages;
    case MarkdownPosts;
    case BladePages;
    case MarkdownPages;
    case DocumentationPages;

    // Frontend Features
    case Darkmode;
    case DocumentationSearch;

    // Integrations
    case Torchlight;

    public static function fromName(string $name): self
    {
        return constant("self::$name");
    }
}
