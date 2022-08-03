<?php

namespace Hyde\Framework\Contracts;

use Hyde\Framework\Models\FrontMatter;

interface MarkdownDocumentContract
{
    /**
     * Get the front matter object, or a value from within.
     *
     * @return \Hyde\Framework\Models\FrontMatter|mixed
     */
    public function matter(string $key = null, mixed $default = null): mixed;

    /**
     * Get the Markdown text body.
     *
     * @return string
     */
    public function body(): string;
}
