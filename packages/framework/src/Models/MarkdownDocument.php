<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Concerns\HasDynamicTitle;
use Hyde\Framework\Contracts\AbstractPage;
use Hyde\Framework\Contracts\MarkdownDocumentContract;

/**
 * The base class for all Markdown-based Page Models.
 *
 * It is, in itself an intermediate object model created by the MarkdownFileService
 * and contains the Front Matter and Markdown body found in a document processed by the service.
 */
class MarkdownDocument extends AbstractPage implements MarkdownDocumentContract
{
    use HasDynamicTitle;

    public array $matter;
    public string $body;
    public string $title;
    public string $slug;

    public static string $fileExtension = '.md';

    public function __construct(array $matter = [], string $body = '', string $title = '', string $slug = '')
    {
        $this->matter = $matter;
        $this->body = $body;
        $this->title = $title;
        $this->slug = $slug;
    }
}
