<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Contracts\FrontMatter\Support\NavigationSchema;

class NavigationData implements NavigationSchema
{
    public string $label;
    public string $group;
    public bool $hidden;
    public int $priority;
}
