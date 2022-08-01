<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\Models\MarkdownDocument;
use Spatie\YamlFrontMatter\YamlFrontMatter;

/**
 * Prepares a Markdown file for further usage by extracting the Front Matter and creating MarkdownDocument object.
 *
 * @see \Hyde\Framework\Testing\Feature\MarkdownFileServiceTest
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
    public string $body = '';

    public function __construct(string $filepath)
    {
        $stream = file_get_contents($filepath);

        // Check if the file has Front Matter.
        if (str_starts_with($stream, '---')) {
            $object = YamlFrontMatter::markdownCompatibleParse($stream);

            if ($object->matter()) {
                $this->matter = $object->matter();
            }

            if ($object->body()) {
                $this->body = $object->body();
            }
        } else {
            $this->body = $stream;
        }
    }

    /**
     * Get the processed Markdown file as a MarkdownDocument.
     */
    public function get(): MarkdownDocument
    {
        return new MarkdownDocument($this->matter, $this->body);
    }
}
