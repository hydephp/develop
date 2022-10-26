<?php

declare(strict_types=1);

namespace Hyde\Framework\Contracts;

use Hyde\Markdown\Models\Markdown;

interface MarkdownDocumentContract
{
    /**
     * Get the front matter object, or a value from within.
     *
     * @return \Hyde\Markdown\Models\FrontMatter|mixed
     */
    public function matter(string $key = null, mixed $default = null): mixed;

    /**
     * Return the document's Markdown object.
     *
     * @return \Hyde\Markdown\Models\Markdown
     */
    public function markdown(): Markdown;
}
