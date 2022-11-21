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
    public readonly int $min;
    public readonly int $max;

    public function __construct(string $type, string $name, string $min, string $max)
    {
        $this->type = $type;
        $this->name = $name;
        $this->min  = intval($min);
        $this->max  = intval($max);
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
            'min'  => (string) $this->min,
            'max'  => (string) $this->max,
        ];
    }
}
