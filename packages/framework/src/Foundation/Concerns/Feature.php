<?php

declare(strict_types=1);

namespace Hyde\Foundation\Concerns;

/**
 * A configurable feature that belongs to the Features class.
 *
 * @see \Hyde\Facades\Features
 *
 * @experimental This class may change significantly before its release.
 */
enum Feature: string
{
    // Page Modules
    case HtmlPages = 'html-pages';
    case MarkdownPosts = 'markdown-posts';
    case BladePages = 'blade-pages';
    case MarkdownPages = 'markdown-pages';
    case DocumentationPages = 'documentation-pages';

    // Frontend Features
    case Darkmode = 'darkmode';
    case DocumentationSearch = 'documentation-search';

    // Integrations
    case Torchlight = 'torchlight';
}
