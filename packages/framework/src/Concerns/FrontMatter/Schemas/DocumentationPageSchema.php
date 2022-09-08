<?php

namespace Hyde\Framework\Concerns\FrontMatter\Schemas;

use Hyde\Framework\Hyde;
use Illuminate\Support\Str;

/**
 * These are the front matter properties that are supported for Hyde documentation pages.
 */
trait DocumentationPageSchema
{
    /**
     * The sidebar category group, if any.
     *
     * Can be overridden in front matter, or by putting the
     * source file in a subdirectory of the same category name.
     */
    public ?string $category = null;

    /**
     * The label for the page shown in the sidebar.
     */
    public ?string $label = null;

    /**
     * Hides the page from the sidebar.
     */
    public ?bool $hidden = null;

    /**
     * The priority of the page used for ordering the sidebar.
     */
    public ?int $priority = null;
}
