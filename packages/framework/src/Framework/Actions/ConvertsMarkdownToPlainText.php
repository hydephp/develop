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
            // Headers
            static::headers(),
            // Fenced codeblocks
            static::fencedCodeblocks(),
            // Strikethrough
            static::Strikethrough(),
            // Fenced codeblocks
            static::fencedCodeblocks2(),
            // Fenced end tags
            static::fencedEndTags(),
            // Remove HTML tags
            static::htmlTags(),
            // Remove setext-style headers
            static::setextHeaders(),
            // Remove footnotes
            static::footnotes(),
            // Remove images
            static::images(),
            // Remove inline links
            static::inlineLinks(),
            // Remove blockquotes
            static::blockquotes(),
            // Remove reference-style links
            static::referenceLinks(),
            // Remove atx-style headers
            static::atxHeaders(),
            // Remove horizontal rules
            static::horizontalRules(),
            // Remove emphasis (repeat the line to remove double emphasis)
            static::emphasis(),
            static::doubleEmphasis(),
            // Remove code blocks
            static::codeBlocks2(),
            // Remove inline code
            static::inlineCode(),
            // Replace two or more newlines with exactly two
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
        return ['/\n={2,}/' => "\n"];
    }

    protected static function fencedCodeblocks(): array
    {
        return ['/~{3}.*\n/' => ''];
    }

    protected static function Strikethrough(): array
    {
        return ['/~~/' => ''];
    }

    protected static function fencedCodeblocks2(): array
    {
        return ['/`{3}.*\n/' => ''];
    }

    protected static function fencedEndTags(): array
    {
        return ['/`{3}/' => ''];
    }

    protected static function htmlTags(): array
    {
        return ['/<[^>]*>/' => ''];
    }

    protected static function setextHeaders(): array
    {
        return ['/^[=\-]{2,}\s*$/' => ''];
    }

    protected static function footnotes(): array
    {
        return ['/\[\^.+?\](\: .*?$)?/' => '', '/\s{0,2}\[.*?\]: .*?$/' => ''];
    }

    protected static function images(): array
    {
        return ['/\!\[(.*?)\][\[\(].*?[\]\)]/' => '$1'];
    }

    protected static function inlineLinks(): array
    {
        return ['/\[(.*?)\][\[\(].*?[\]\)]/' => '$1'];
    }

    protected static function blockquotes(): array
    {
        return ['/^\s{0,3}>\s?/' => ''];
    }

    protected static function referenceLinks(): array
    {
        return ['/^\s{1,2}\[(.*?)\]: (\S+)( ".*?")?\s*$/' => ''];
    }

    protected static function atxHeaders(): array
    {
        return ['/^(\n)?\s{0,}#{1,6}\s+| {0,}(\n)?\s{0,}#{0,} {0,}(\n)?\s{0,}$/m' => '$1$2$3'];
    }

    protected static function horizontalRules(): array
    {
        return ['/^(-\s*?|\*\s*?|_\s*?){3,}\s*/m' => ''];
    }

    protected static function emphasis(): array
    {
        return ['/([\*_]{1,3})(\S.*?\S{0,1})\1/' => '$2'];
    }

    protected static function doubleEmphasis(): array
    {
        return ['/([\*_]{1,3})(\S.*?\S{0,1})\1/' => '$2'];
    }

    protected static function codeBlocks2(): array
    {
        return ['/(`{3,})(.*?)\1/m' => '$2'];
    }

    protected static function inlineCode(): array
    {
        return ['/`(.+?)`/' => '$1'];
    }

    protected static function repeatedNewlines(): array
    {
        return ['/\n{2,}/' => "\n\n"];
    }
}
