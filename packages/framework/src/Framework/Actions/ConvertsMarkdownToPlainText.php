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
    protected const ATX_HEADERS = ['/^(\n)?\s{0,}#{1,6}\s+| {0,}(\n)?\s{0,}#{0,} {0,}(\n)?\s{0,}$/m' => '$1$2$3'];

    protected const SETEXT_HEADERS = ['/\n={2,}/' => "\n"];

    protected const HORIZONTAL_RULES = ['/^(-\s*?|\*\s*?|_\s*?){3,}\s*/m' => ''];

    protected const HTML_TAGS = ['/<[^>]*>/' => ''];

    protected const CODE_BLOCKS = ['/(`{3,})(.*?)\1/m' => '$2'];

    protected const FENCED_CODEBLOCKS = ['/`{3}.*\n/' => ''];

    protected const FENCED_END_TAGS = ['/`{3}/' => ''];

    protected const TILDE_FENCED_CODEBLOCKS = ['/~{3}.*\n/' => ''];

    protected const TILDE_FENCED_END_TAGS = ['/~{3}/' => ''];

    protected const INLINE_CODE = ['/`(.+?)`/' => '$1'];

    protected const IMAGES = ['/\!\[(.*?)\][\[\(].*?[\]\)]/' => '$1'];

    protected const INLINE_LINKS = ['/\[(.*?)\][\[\(].*?[\]\)]/' => '$1'];

    protected const REFERENCE_LINKS = ['/^\s{1,2}\[(.*?)\]: (\S+)( ".*?")?\s*$/' => ''];

    protected const EMPHASIS = ['/([\*_]{1,3})(\S.*?\S{0,1})\1/' => '$2'];

    /** Emphasis (repeat the line to remove double emphasis) */
    protected const DOUBLE_EMPHASIS = self::EMPHASIS;

    protected const STRIKETHROUGH = ['/~~/' => ''];

    protected const BLOCKQUOTES = ['/^\s{0,3}>\s?/' => ''];

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
            static::ATX_HEADERS,
            static::SETEXT_HEADERS,
            static::HORIZONTAL_RULES,
            static::HTML_TAGS,
            static::CODE_BLOCKS,
            static::FENCED_CODEBLOCKS,
            static::FENCED_END_TAGS,
            static::TILDE_FENCED_CODEBLOCKS,
            static::TILDE_FENCED_END_TAGS,
            static::INLINE_CODE,
            static::IMAGES,
            static::INLINE_LINKS,
            static::REFERENCE_LINKS,
            static::EMPHASIS,
            static::DOUBLE_EMPHASIS,
            static::STRIKETHROUGH,
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
