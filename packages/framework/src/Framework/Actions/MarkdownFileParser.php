<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Hyde;
use Hyde\Markdown\Models\MarkdownDocument;
use Spatie\YamlFrontMatter\YamlFrontMatter;

/**
 * Prepares a Markdown file for further usage by extracting the Front Matter and creating MarkdownDocument object.
 *
 * @see \Hyde\Framework\Testing\Feature\MarkdownFileParserTest
 */
class MarkdownFileParser
{
    /**
     * The extracted Front Matter.
     *
     * @var array
     */
    public array $matter = [];

    /**
     * The extracted Markdown body.
     *
     * @var string
     */
    public string $markdown = '';

    public function __construct(string $localFilepath)
    {
        $stream = file_get_contents(Hyde::path($localFilepath));

        // Check if the file has Front Matter.
        if (str_starts_with($stream, '---')) {
            $object = YamlFrontMatter::markdownCompatibleParse($stream);

            if ($object->matter()) {
                $this->matter = $object->matter();

                // Unset the slug from the matter, as it can cause problems if it exists.
                unset($this->matter['slug']);
            }

            if ($object->body()) {
                $this->markdown = $object->body();
            }
        } else {
            $this->markdown = $stream;
        }
    }

    /**
     * Get the processed Markdown file as a MarkdownDocument.
     */
    public function get(): MarkdownDocument
    {
        return new MarkdownDocument($this->matter, $this->markdown);
    }

    public static function parse(string $filepath): MarkdownDocument
    {
        return (new self($filepath))->get();
    }
}
