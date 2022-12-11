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
        'label'           => 'string',
        'group|category'  => 'string',
        'hidden'          => 'bool',
        'priority'        => 'int',
    ];
}
