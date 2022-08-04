<?php

namespace Hyde\Framework\Contracts;

use Hyde\Framework\Models\FrontMatter;
use Hyde\Framework\Models\Markdown;

interface MarkdownPageContract
{
    /**
     * Alternative to constructor, using primitive data types.
     * This method will construct then return a new instance of the class.
     *
     * @param  string  $identifier
     * @param  array  $matter
     * @param  string  $body
     * @return \Hyde\Framework\Contracts\MarkdownPageContract
     */
    public static function make(string $identifier = '', array $matter = [], string $body = ''): static;

    /**
     * Construct a new MarkdownPage object from constructed data types.
     * Normally, this is done by the SourceFileParser.
     *
     * @see \Hyde\Framework\Actions\SourceFileParser
     *
     * The types are strictly enforced to ensure a predictable behavior and constant access interface.
     *
     * @param  string  $identifier
     * @param  \Hyde\Framework\Models\FrontMatter|null  $matter
     * @param  \Hyde\Framework\Models\Markdown|null  $markdown
     */
    public function __construct(string $identifier = '', ?FrontMatter $matter = null, ?Markdown $markdown = null);

    /**
     * Get the value of the specified key from the front matter.
     *
     * @param  string  $name
     * @return mixed
     */
    public function __get(string $name);
}
