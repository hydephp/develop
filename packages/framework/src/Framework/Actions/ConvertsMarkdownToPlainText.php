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
    /** Headers */
    protected const HEADERS = ['/\n={2,}/' => "\n"];

    /** Remove atx-style headers */
    protected const ATX_HEADERS = ['/^(\n)?\s{0,}#{1,6}\s+| {0,}(\n)?\s{0,}#{0,} {0,}(\n)?\s{0,}$/m' => '$1$2$3'];

    /** Remove setext-style headers */
    protected const SETEXT_HEADERS = ['/^[=\-]{2,}\s*$/' => ''];

    /** Remove horizontal rules */
    protected const HORIZONTAL_RULES = ['/^(-\s*?|\*\s*?|_\s*?){3,}\s*/m' => ''];

    /** Remove HTML tags */
    protected const HTML_TAGS = ['/<[^>]*>/' => ''];

    /** Remove code blocks */
    protected const CODE_BLOCKS_2 = ['/(`{3,})(.*?)\1/m' => '$2'];

    /** Fenced codeblocks */
    protected const FENCED_CODEBLOCKS = ['/~{3}.*\n/' => ''];

    /** Fenced codeblocks */
    protected const FENCED_CODEBLOCKS_2 = ['/`{3}.*\n/' => ''];

    /** Fenced end tags */
    protected const FENCED_END_TAGS = ['/`{3}/' => ''];

    /** Remove inline code */
    protected const INLINE_CODE = ['/`(.+?)`/' => '$1'];

    /** Remove images */
    protected const IMAGES = ['/\!\[(.*?)\][\[\(].*?[\]\)]/' => '$1'];

    /** Remove inline links */
    protected const INLINE_LINKS = ['/\[(.*?)\][\[\(].*?[\]\)]/' => '$1'];

    /** Remove reference-style links */
    protected const REFERENCE_LINKS = ['/^\s{1,2}\[(.*?)\]: (\S+)( ".*?")?\s*$/' => ''];

    /** Remove emphasis (repeat the line to remove double emphasis) */
    protected const EMPHASIS = ['/([\*_]{1,3})(\S.*?\S{0,1})\1/' => '$2'];

    /** Strikethrough */
    protected const STRIKETHROUGH = ['/~~/' => ''];

    /** Remove emphasis (repeat the line to remove double emphasis) */
    protected const DOUBLE_EMPHASIS = ['/([\*_]{1,3})(\S.*?\S{0,1})\1/' => '$2'];

    /** Remove blockquotes */
    protected const BLOCKQUOTES = ['/^\s{0,3}>\s?/' => ''];

    /** Remove footnotes */
    protected const FOOTNOTES = ['/\[\^.+?\](\: .*?$)?/' => '', '/\s{0,2}\[.*?\]: .*?$/' => ''];

    /** Replace two or more newlines with exactly two */
    protected const REPEATED_NEWLINES = ['/\n{2,}/' => "\n\n"];

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
            static::HEADERS,
            static::ATX_HEADERS,
            static::SETEXT_HEADERS,
            static::HORIZONTAL_RULES,
            static::HTML_TAGS,
            static::CODE_BLOCKS_2,
            static::FENCED_CODEBLOCKS,
            static::FENCED_CODEBLOCKS_2,
            static::FENCED_END_TAGS,
            static::INLINE_CODE,
            static::IMAGES,
            static::INLINE_LINKS,
            static::REFERENCE_LINKS,
            static::EMPHASIS,
            static::STRIKETHROUGH,
            static::DOUBLE_EMPHASIS,
            static::BLOCKQUOTES,
            static::FOOTNOTES,
            static::REPEATED_NEWLINES,
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
}
