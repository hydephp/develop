<?php

declare(strict_types=1);

namespace Hyde\Enums;

/**
 * A configurable feature that belongs to the Features class.
 *
 * @see \Hyde\Facades\Features
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

    /** @deprecated This method will be removed in HydePHP v2.0 */
    public static function match(string $name): self
    {
        return match ($name) {
            'html-pages' => self::HtmlPages,
            'markdown-posts' => self::MarkdownPosts,
            'blade-pages' => self::BladePages,
            'markdown-pages' => self::MarkdownPages,
            'documentation-pages' => self::DocumentationPages,
            'darkmode' => self::Darkmode,
            'documentation-search' => self::DocumentationSearch,
            'torchlight' => self::Torchlight,
        };
    }
}
