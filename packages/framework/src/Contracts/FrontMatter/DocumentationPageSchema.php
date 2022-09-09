<?php

namespace Hyde\Framework\Contracts\FrontMatter;

interface DocumentationPageSchema
{
    public const DOCUMENTATION_PAGE_SCHEMA = [
        'category'  => 'string',
        'label'     => 'string',
        'hidden'    => 'bool',
        'priority'  => 'int',
    ];
}
