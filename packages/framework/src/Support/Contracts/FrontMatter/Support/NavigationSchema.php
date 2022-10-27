<?php

declare(strict_types=1);

namespace Hyde\Support\Contracts\FrontMatter\Support;

/**
 * @see \Hyde\Framework\Features\Navigation\NavigationData
 * @see \Hyde\Framework\Concerns\HydePage
 */
interface NavigationSchema
{
    public const NAVIGATION_SCHEMA = [
        'label'     => 'string',
        'group'     => 'string',
        'hidden'    => 'bool',
        'priority'  => 'int',
    ];
}
