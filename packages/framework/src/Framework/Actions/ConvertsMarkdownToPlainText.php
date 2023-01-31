<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use function explode;
use function implode;
use function preg_replace;
use function str_replace;
use function strip_tags;

/**
 * Converts Markdown to plain text.
 *
 * @see \Hyde\Framework\Testing\Feature\Actions\ConvertsMarkdownToPlainTextTest
 *
 * @experimental This class is experimental and does not have a stable API yet.
 *
 * @internal This class is experimental and should not be used outside HydePHP.
 */
class ConvertsMarkdownToPlainText
{
    protected string $markdown;

    public function __construct(string $markdown)
    {
        $this->markdown = $markdown;
    }

    /**
     * Regex based on https://github.com/stiang/remove-markdown, licensed under MIT.
     */
    public function execute(): string
    {
        // Remove any HTML tags
        $markdown = strip_tags($this->markdown);

        $patterns = [
            static::headers(),
            static::fencedCodeblocks(),
            static::Strikethrough(),
            static::fencedCodeblocks2(),
            static::fencedEndTags(),
            static::htmlTags(),
            static::setextHeaders(),
            static::footnotes(),
            static::images(),
            static::inlineLinks(),
            static::blockquotes(),
            static::referenceLinks(),
            static::atxHeaders(),
            static::horizontalRules(),
            static::emphasis(),
            static::doubleEmphasis(),
            static::codeBlocks2(),
            static::inlineCode(),
            static::repeatedNewlines(),
        ];

        foreach ($patterns as $pattern) {
            $markdown = preg_replace(array_keys($pattern), array_values($pattern), $markdown) ?? $markdown;
        }

        $lines = explode("\n", $markdown);
        foreach ($lines as $line => $contents) {
            $newContents = $contents;
            // Remove tables (dividers)
            if (str_starts_with($newContents, '|--') && str_ends_with($newContents, '--|')) {
                $newContents = str_replace(['|', '-'], ['', ''], $newContents);
            }
            // Remove tables (cells)
            if (str_starts_with($newContents, '| ') && str_ends_with($newContents, '|')) {
                $newContents = rtrim(str_replace(['| ', ' | ', ' |'], ['', '', ''], $newContents), ' ');
            }

            // Remove blockquotes
            if (str_starts_with($newContents, '> ')) {
                $newContents = substr($newContents, 2);
            }
            // Remove multiline blockquotes
            if (str_starts_with($newContents, '>')) {
                $newContents = substr($newContents, 1);
            }
            $lines[$line] = $newContents;
        }

        return implode("\n", $lines);
    }

    /** Headers */
    protected static function headers(): array
    {
        return ['/\n={2,}/' => "\n"];
    }

    /** Fenced codeblocks */
    protected static function fencedCodeblocks(): array
    {
        return ['/~{3}.*\n/' => ''];
    }

    /** Strikethrough */
    protected static function Strikethrough(): array
    {
        return ['/~~/' => ''];
    }

    /** Fenced codeblocks */
    protected static function fencedCodeblocks2(): array
    {
        return ['/`{3}.*\n/' => ''];
    }

    /** Fenced end tags */
    protected static function fencedEndTags(): array
    {
        return ['/`{3}/' => ''];
    }

    /** Remove HTML tags */
    protected static function htmlTags(): array
    {
        return ['/<[^>]*>/' => ''];
    }

    /** Remove setext-style headers */
    protected static function setextHeaders(): array
    {
        return ['/^[=\-]{2,}\s*$/' => ''];
    }

    /** Remove footnotes */
    protected static function footnotes(): array
    {
        return ['/\[\^.+?\](\: .*?$)?/' => '', '/\s{0,2}\[.*?\]: .*?$/' => ''];
    }

    /** Remove images */
    protected static function images(): array
    {
        return ['/\!\[(.*?)\][\[\(].*?[\]\)]/' => '$1'];
    }

    /** Remove inline links */
    protected static function inlineLinks(): array
    {
        return ['/\[(.*?)\][\[\(].*?[\]\)]/' => '$1'];
    }

    /** Remove blockquotes */
    protected static function blockquotes(): array
    {
        return ['/^\s{0,3}>\s?/' => ''];
    }

    /** Remove reference-style links */
    protected static function referenceLinks(): array
    {
        return ['/^\s{1,2}\[(.*?)\]: (\S+)( ".*?")?\s*$/' => ''];
    }

    /** Remove atx-style headers */
    protected static function atxHeaders(): array
    {
        return ['/^(\n)?\s{0,}#{1,6}\s+| {0,}(\n)?\s{0,}#{0,} {0,}(\n)?\s{0,}$/m' => '$1$2$3'];
    }

    /** Remove horizontal rules */
    protected static function horizontalRules(): array
    {
        return ['/^(-\s*?|\*\s*?|_\s*?){3,}\s*/m' => ''];
    }

    /** Remove emphasis (repeat the line to remove double emphasis) */
    protected static function emphasis(): array
    {
        return ['/([\*_]{1,3})(\S.*?\S{0,1})\1/' => '$2'];
    }

    /** Remove emphasis (repeat the line to remove double emphasis) */
    protected static function doubleEmphasis(): array
    {
        return ['/([\*_]{1,3})(\S.*?\S{0,1})\1/' => '$2'];
    }

    /** Remove code blocks */
    protected static function codeBlocks2(): array
    {
        return ['/(`{3,})(.*?)\1/m' => '$2'];
    }

    /** Remove inline code */
    protected static function inlineCode(): array
    {
        return ['/`(.+?)`/' => '$1'];
    }

    /** Replace two or more newlines with exactly two */
    protected static function repeatedNewlines(): array
    {
        return ['/\n{2,}/' => "\n\n"];
    }
}
