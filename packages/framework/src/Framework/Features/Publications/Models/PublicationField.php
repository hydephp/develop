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

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'min'  => $this->min,
            'max'  => $this->max,
            'type' => $this->type,
        ];
    }
}
