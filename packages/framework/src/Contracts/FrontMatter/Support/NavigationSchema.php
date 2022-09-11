<?php

namespace Hyde\Framework\Contracts\FrontMatter\Support;

interface NavigationSchema
{
    public const NAVIGATION_SCHEMA = [
        'label'     => 'string',
        'group'     => 'string',
        'hidden'    => 'bool',
        'priority'  => 'int',
    ];
}
