<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories;

use Hyde\Markdown\Contracts\FrontMatter\PageSchema;

class HydePageDataFactory extends Concerns\PageDataFactory implements PageSchema
{
    public function toArray(): array
    {
        // TODO: Implement toArray() method.
    }
}
