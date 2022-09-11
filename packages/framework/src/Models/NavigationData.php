<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Contracts\FrontMatter\Support\NavigationSchema;

class NavigationData implements NavigationSchema
{
    public ?string $label = null;
    public ?string $group = null;
    public ?bool $hidden = null;
    public ?int $priority = null;

    public function __construct(?string $label, ?string $group, ?bool $hidden, ?int $priority)
    {
        $this->label = $label;
        $this->group = $group;
        $this->hidden = $hidden;
        $this->priority = $priority;
    }
}
