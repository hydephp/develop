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
            '/\n={2,}/' => "\n",
            // Fenced codeblocks
            '/~{3}.*\n/' => '',
            // Strikethrough
            '/~~/' => '',
            // Fenced codeblocks
            '/`{3}.*\n/' => '',
            // Fenced end tags
            '/`{3}/' => '',
            // Remove HTML tags
            '/<[^>]*>/' => '',
            // Remove setext-style headers
            '/^[=\-]{2,}\s*$/' => '',
            // Remove footnotes?
            '/\[\^.+?\](\: .*?$)?/' => '',
            '/\s{0,2}\[.*?\]: .*?$/' => '',
            // Remove images
            '/\!\[(.*?)\][\[\(].*?[\]\)]/' => '$1',
            // Remove inline links
            '/\[(.*?)\][\[\(].*?[\]\)]/' => '$1',
            // Remove blockquotes
            '/^\s{0,3}>\s?/' => '',
            // Remove reference-style links?
            '/^\s{1,2}\[(.*?)\]: (\S+)( ".*?")?\s*$/' => '',
            // Remove atx-style headers
            '/^(\n)?\s{0,}#{1,6}\s+| {0,}(\n)?\s{0,}#{0,} {0,}(\n)?\s{0,}$/m' => '$1$2$3',
            // Remove emphasis
            '/([\*_]{1,3})(\S.*?\S{0,1})\1/' => '$2',
            // Remove code blocks
            '/(`{3,})(.*?)\1/m' => '$2',
            // Remove inline code
            '/`(.+?)`/' => '$1',
            // Replace two or more newlines with exactly two
            '/\n{2,}/' => "\n\n",
            // Remove horizontal rules
            '/^(-\s*?|\*\s*?|_\s*?){3,}\s*/m' => '',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $markdown = preg_replace($pattern, $replacement, $markdown) ?? $markdown;
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
                $newContents = str_replace(['| ', ' | ', ' |'], ['', '', ''], $newContents);
            }
            $lines[$line] = $newContents;
        }

        return implode("\n", $lines);
    }
}
