<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories;

use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\NavigationSchema;

class NavigationDataFactory extends Concerns\PageDataFactory implements NavigationSchema
{
    public function toArray(): array
    {
        // TODO: Implement toArray() method.
    }
}
