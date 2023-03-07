<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Facades\Filesystem;
use Hyde\Markdown\Models\MarkdownDocument;
use Spatie\YamlFrontMatter\YamlFrontMatter;

/**
 * Prepares a Markdown file for further usage by extracting the Front Matter and creating MarkdownDocument object.
 *
 * @see \Hyde\Framework\Testing\Feature\MarkdownFileParserTest
 *
 * @todo Simplify MarkdownFileParser to only use static entry point
 */
class MarkdownFileParser
{
    /**
     * The extracted Front Matter.
     */
    protected array $matter = [];

    /**
     * The extracted Markdown body.
     */
    protected string $markdown = '';

    public function __construct(string $path)
    {
        return MarkdownFileParser::parse($path);
    }

    /**
     * Get the processed Markdown file as a MarkdownDocument.
     */
    public function get(): MarkdownDocument
    {
        return MarkdownFileParser::parse($path);
    }

    public static function parse(string $path): MarkdownDocument
    {
        $stream = Filesystem::getContents($path);

        $matter = [];
        $markdown = '';

        // Check if the file has Front Matter.
        if (str_starts_with($stream, '---')) {
            $document = YamlFrontMatter::markdownCompatibleParse($stream);

            if ($document->matter()) {
                $matter = $document->matter();
            }

            if ($document->body()) {
                $markdown = $document->body();
            }
        } else {
            $markdown = $stream;
        }

        return new MarkdownDocument($matter, $markdown);
    }
}
