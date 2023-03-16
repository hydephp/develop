<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Facades\Filesystem;
use Hyde\Markdown\Models\MarkdownDocument;
use Spatie\YamlFrontMatter\YamlFrontMatter;

use function str_starts_with;

/**
 * Prepares a Markdown file for further usage by extracting the Front Matter
 * and Markdown body, and creating MarkdownDocument object from them.
 */
class MarkdownFileParser
{
    /**
     * @param  string  $path  The path to the Markdown file tp parse.
     * @return MarkdownDocument The processed Markdown file as a MarkdownDocument.
     */
    public static function parse(string $path): MarkdownDocument
    {
        return (new static($path))->get();
    }

    /**
     * The extracted Front Matter.
     */
    protected array $matter = [];

    /**
     * The extracted Markdown body.
     */
    protected string $markdown = '';

    protected function __construct(string $path)
    {
        $stream = Filesystem::getContents($path);

        // Check if the file has Front Matter.
        if (str_starts_with($stream, '---')) {
            $document = YamlFrontMatter::markdownCompatibleParse($stream);

            if ($document->matter()) {
                $this->matter = $document->matter();
            }

            if ($document->body()) {
                $this->markdown = $document->body();
            }
        } else {
            $this->markdown = $stream;
        }
    }

    protected function get(): MarkdownDocument
    {
        return new MarkdownDocument($this->matter, $this->markdown);
    }
}
