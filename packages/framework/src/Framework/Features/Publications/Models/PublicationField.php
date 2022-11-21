<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models;

use Hyde\Support\Concerns\JsonSerializesArrayable;
use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use JsonSerializable;

/**
 * @see \Hyde\Framework\Testing\Feature\PublicationFieldTest
 */
class PublicationField implements JsonSerializable, Arrayable
{
    use JsonSerializesArrayable;

    public final const TYPES = ['string', 'boolean', 'integer', 'float', 'datetime', 'url', 'array', 'text', 'image'];

    public readonly string $type;
    public readonly string $name;
    public readonly ?int $min;
    public readonly ?int $max;

    public function __construct(string $type, string $name, ?int $min, ?int $max)
    {
        if (!in_array($type, self::TYPES)) {
            throw new InvalidArgumentException(sprintf("The type '$type' is not a valid type. Valid types are: %s.", implode(', ', self::TYPES)));
        }

        if (($min !== null) && ($max !== null) && $min > $max) {
            throw new InvalidArgumentException("The 'max' value cannot be less than the 'min' value.");
        }

        $this->type = $type;
        $this->name = $name;
        $this->min = $min;
        $this->max = $max;
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

    public function validateInputAgainstRules(string $input): bool
    {
        // TODO: Implement this method.

        return true;
    }
}
