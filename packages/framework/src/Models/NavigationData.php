<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Contracts\FrontMatter\Support\NavigationSchema;

class NavigationData implements NavigationSchema
{
    public ?string $label = null;
    public ?string $group = null;
    public ?bool $hidden = null;
    public ?int $priority = null;
}
