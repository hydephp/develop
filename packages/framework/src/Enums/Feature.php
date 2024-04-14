<?php

declare(strict_types=1);

namespace Hyde\Enums;

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
}
