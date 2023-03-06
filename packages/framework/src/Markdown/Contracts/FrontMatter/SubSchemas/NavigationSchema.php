<?php

declare(strict_types=1);

namespace Hyde\Markdown\Contracts\FrontMatter\SubSchemas;

/**
 * @see \Hyde\Framework\Features\Navigation\NavigationData
 * @see \Hyde\Pages\Concerns\HydePage
 */
interface NavigationSchema
{
    public const NAVIGATION_SCHEMA = [
        'label'     => 'string',
        'priority'  => 'int',  // Order is also supported
        'hidden'    => 'bool',  // Visible is also supported (but obviously invert the value)
        'group'     => 'string', // Category is also supported
    ];
}
