<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models;

/**
 * @see \Hyde\Framework\Testing\Feature\PublicationFieldTest
 */
class PublicationField
{
    public readonly string $name;
    public readonly string $min;
    public readonly string $max;
    public readonly string $type;

    public function __construct(string $name, string $min, string $max, string $type)
    {
        $this->name = $name;
        $this->min  = $min;
        $this->max  = $max;
        $this->type = $type;
    }
}
