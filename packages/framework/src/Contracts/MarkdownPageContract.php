<?php

namespace Hyde\Framework\Contracts;

use Hyde\Framework\Models\FrontMatter;
use Hyde\Framework\Models\MarkdownDocument;

interface MarkdownPageContract
{
    /**
     * Construct a new MarkdownPage object. Normally, this is done by the SourceFileParser.
     *
     * @see \Hyde\Framework\Actions\SourceFileParser
     *
     * @param string $identifier
     * @param \Hyde\Framework\Models\FrontMatter|null $matter
     * @param \Hyde\Framework\Models\MarkdownDocument|null $markdownDocument
     */
    public function __construct(string $identifier = '', ?FrontMatter $matter = null, ?MarkdownDocument $markdownDocument = null);
}