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
            $this->headers(),
            // Fenced codeblocks
            $this->fencedCodeblocks(),
            // Strikethrough
            $this->Strikethrough(),
            // Fenced codeblocks
            $this->fencedCodeblocks2(),
            // Fenced end tags
            $this->fencedEndTags(),
            // Remove HTML tags
            $this->htmlTags(),
            // Remove setext-style headers
            $this->setextHeaders(),
            // Remove footnotes
            $this->footnotes(),
            // Remove images
            $this->images(),
            // Remove inline links
            $this->inlineLinks(),
            // Remove blockquotes
            $this->blockquotes(),
            // Remove reference-style links
            $this->referenceLinks(),
            // Remove atx-style headers
            $this->atxHeaders(),
            // Remove horizontal rules
            $this->horizontalRules(),
            // Remove emphasis (repeat the line to remove double emphasis)
            $this->emphasis(),
            $this->doubleEmphasis(),
            // Remove code blocks
            $this->codeBlocks2(),
            // Remove inline code
            $this->inlineCode(),
            // Replace two or more newlines with exactly two
            $this->repeatedNewlines(),
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

    protected function headers(): array
    {
        return ['/\n={2,}/' => "\n"];
    }

    protected function fencedCodeblocks(): array
    {
        return ['/~{3}.*\n/' => ''];
    }

    protected function Strikethrough(): array
    {
        return ['/~~/' => ''];
    }

    protected function fencedCodeblocks2(): array
    {
        return ['/`{3}.*\n/' => ''];
    }

    protected function fencedEndTags(): array
    {
        return ['/`{3}/' => ''];
    }

    protected function htmlTags(): array
    {
        return ['/<[^>]*>/' => ''];
    }

    protected function setextHeaders(): array
    {
        return ['/^[=\-]{2,}\s*$/' => ''];
    }

    protected function footnotes(): array
    {
        return ['/\[\^.+?\](\: .*?$)?/' => '', '/\s{0,2}\[.*?\]: .*?$/' => ''];
    }

    protected function images(): array
    {
        return ['/\!\[(.*?)\][\[\(].*?[\]\)]/' => '$1'];
    }

    protected function inlineLinks(): array
    {
        return ['/\[(.*?)\][\[\(].*?[\]\)]/' => '$1'];
    }

    protected function blockquotes(): array
    {
        return ['/^\s{0,3}>\s?/' => ''];
    }

    protected function referenceLinks(): array
    {
        return ['/^\s{1,2}\[(.*?)\]: (\S+)( ".*?")?\s*$/' => ''];
    }

    protected function atxHeaders(): array
    {
        return ['/^(\n)?\s{0,}#{1,6}\s+| {0,}(\n)?\s{0,}#{0,} {0,}(\n)?\s{0,}$/m' => '$1$2$3'];
    }

    protected function horizontalRules(): array
    {
        return ['/^(-\s*?|\*\s*?|_\s*?){3,}\s*/m' => ''];
    }

    protected function emphasis(): array
    {
        return ['/([\*_]{1,3})(\S.*?\S{0,1})\1/' => '$2'];
    }

    protected function doubleEmphasis(): array
    {
        return ['/([\*_]{1,3})(\S.*?\S{0,1})\1/' => '$2'];
    }

    protected function codeBlocks2(): array
    {
        return ['/(`{3,})(.*?)\1/m' => '$2'];
    }

    protected function inlineCode(): array
    {
        return ['/`(.+?)`/' => '$1'];
    }

    protected function repeatedNewlines(): array
    {
        return ['/\n{2,}/' => "\n\n"];
    }
}
