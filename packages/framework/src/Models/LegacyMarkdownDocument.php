<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Concerns\HasDynamicTitle;
use Hyde\Framework\Contracts\AbstractPage;
use Hyde\Framework\Contracts\MarkdownDocumentContract;

/**
 * The base class for all Markdown-based Page Models.
 * @deprecated v0.44.0 - Extend AbstractMarkdownPage instead.
 *
 * It is, in itself an intermediate object model created by the MarkdownFileService
 * and contains the Front Matter and Markdown body found in a document processed by the service.
 *
 * @see \Hyde\Framework\Models\MarkdownPage
 * @see \Hyde\Framework\Models\MarkdownPost
 * @see \Hyde\Framework\Models\DocumentationPage
 */
class LegacyMarkdownDocument extends AbstractPage implements MarkdownDocumentContract
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
