<?php

declare(strict_types=1);

namespace Hyde\Markdown\Contracts\FrontMatter\SubSchemas;

use Hyde\Markdown\Contracts\FrontMatter\PageSchema;

/**
 * @see \Hyde\Framework\Features\Navigation\NavigationData
 * @see \Hyde\Pages\Concerns\HydePage
 */
interface NavigationSchema extends PageSchema
{
    public const NAVIGATION_SCHEMA = [
        'label' => 'string', // The text to display
        'priority' => 'int',  // Order is also supported
        'hidden' => 'bool',  // Visible is also supported (but obviously invert the value)
        'group' => 'string', // Category is also supported
    ];
}
