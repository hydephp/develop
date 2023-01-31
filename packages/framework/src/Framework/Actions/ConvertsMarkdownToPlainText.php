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

    protected static function headers(): array
    {
        // Headers
        return ['/\n={2,}/' => "\n"];
    }

    protected static function fencedCodeblocks(): array
    {
        // Fenced codeblocks
        return ['/~{3}.*\n/' => ''];
    }

    protected static function Strikethrough(): array
    {
        // Strikethrough
        return ['/~~/' => ''];
    }

    protected static function fencedCodeblocks2(): array
    {
        // Fenced codeblocks
        return ['/`{3}.*\n/' => ''];
    }

    protected static function fencedEndTags(): array
    {
        // Fenced end tags
        return ['/`{3}/' => ''];
    }

    protected static function htmlTags(): array
    {
        // Remove HTML tags
        return ['/<[^>]*>/' => ''];
    }

    protected static function setextHeaders(): array
    {
        // Remove setext-style headers
        return ['/^[=\-]{2,}\s*$/' => ''];
    }

    protected static function footnotes(): array
    {
        // Remove footnotes
        return ['/\[\^.+?\](\: .*?$)?/' => '', '/\s{0,2}\[.*?\]: .*?$/' => ''];
    }

    protected static function images(): array
    {
        // Remove images
        return ['/\!\[(.*?)\][\[\(].*?[\]\)]/' => '$1'];
    }

    protected static function inlineLinks(): array
    {
        // Remove inline links
        return ['/\[(.*?)\][\[\(].*?[\]\)]/' => '$1'];
    }

    protected static function blockquotes(): array
    {
        // Remove blockquotes
        return ['/^\s{0,3}>\s?/' => ''];
    }

    protected static function referenceLinks(): array
    {
        // Remove reference-style links
        return ['/^\s{1,2}\[(.*?)\]: (\S+)( ".*?")?\s*$/' => ''];
    }

    protected static function atxHeaders(): array
    {
        // Remove atx-style headers
        return ['/^(\n)?\s{0,}#{1,6}\s+| {0,}(\n)?\s{0,}#{0,} {0,}(\n)?\s{0,}$/m' => '$1$2$3'];
    }

    protected static function horizontalRules(): array
    {
        // Remove horizontal rules
        return ['/^(-\s*?|\*\s*?|_\s*?){3,}\s*/m' => ''];
    }

    protected static function emphasis(): array
    {
        // Remove emphasis (repeat the line to remove double emphasis)
        return ['/([\*_]{1,3})(\S.*?\S{0,1})\1/' => '$2'];
    }

    protected static function doubleEmphasis(): array
    {
        // Remove emphasis (repeat the line to remove double emphasis)
        return ['/([\*_]{1,3})(\S.*?\S{0,1})\1/' => '$2'];
    }

    protected static function codeBlocks2(): array
    {
        // Remove code blocks
        return ['/(`{3,})(.*?)\1/m' => '$2'];
    }

    protected static function inlineCode(): array
    {
        // Remove inline code
        return ['/`(.+?)`/' => '$1'];
    }

    protected static function repeatedNewlines(): array
    {
        // Replace two or more newlines with exactly two
        return ['/\n{2,}/' => "\n\n"];
    }
}
