<?php

namespace Hyde\Framework\Contracts;

use Hyde\Framework\Models\FrontMatter;
use Hyde\Framework\Models\Markdown;

interface MarkdownPageContract
{
    /**
     * Construct a new MarkdownPage object. Normally, this is done by the SourceFileParser.
     *
     * @see \Hyde\Framework\Actions\SourceFileParser
     *
     * @param  string  $identifier
     * @param  \Hyde\Framework\Models\FrontMatter|null  $matter
     * @param  \Hyde\Framework\Models\Markdown|null  $markdown
     */
    public function __construct(string $identifier = '', ?FrontMatter $matter = null, ?Markdown $markdown = null);
}
