<?php

declare(strict_types=1);

namespace Hyde\Framework\Contracts\FrontMatter\Support;

/**
 * @see \Hyde\Navigation\NavigationData
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
