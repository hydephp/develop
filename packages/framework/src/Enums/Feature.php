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
