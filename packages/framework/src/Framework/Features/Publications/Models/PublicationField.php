<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models;

use Hyde\Support\Concerns\JsonSerializesArrayable;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * @see \Hyde\Framework\Testing\Feature\PublicationFieldTest
 */
class PublicationField implements JsonSerializable, Arrayable
{
    use JsonSerializesArrayable;

    public readonly string $type;
    public readonly string $name;
    public readonly string $min;
    public readonly string $max;

    public function __construct(string $type, string $name, string $min, string $max)
    {
        $this->type = $type;
        $this->name = $name;
        $this->min  = $min;
        $this->max  = $max;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
            'min'  => $this->min,
            'max'  => $this->max,
        ];
    }
}
