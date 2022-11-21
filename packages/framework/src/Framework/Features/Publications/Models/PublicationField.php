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

    protected readonly string $type;
    protected readonly string $name;
    protected readonly int $min;
    protected readonly int $max;

    public function __construct(string $type, string $name, string $min, string $max)
    {
        $this->type = $type;
        $this->name = $name;
        $this->min  = intval($min);
        $this->max  = intval($max);
    }

    public function __get(string $name): string
    {
        return (string) $this->$name;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->__get('type'),
            'name' => $this->__get('name'),
            'min'  => $this->__get('min'),
            'max'  => $this->__get('max'),
        ];
    }
}
