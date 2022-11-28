<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models;

use Hyde\Support\Concerns\Serializable;
use Hyde\Support\Contracts\SerializableContract;
use Illuminate\Support\Str;
use InvalidArgumentException;
use function strtolower;

/**
 * @see \Hyde\Framework\Testing\Feature\PublicationFieldTypeTest
 */
class PublicationFieldType implements SerializableContract
{
    use Serializable;

    public final const TYPES = ['string', 'boolean', 'integer', 'float', 'datetime', 'url', 'array', 'text', 'image', 'tag'];
    public final const DEFAULT_RULES = [
        'string'   => ['required', 'string', 'between'],
        'boolean'  => ['required', 'boolean'],
        'integer'  => ['required', 'integer', 'between'],
        'float'    => ['required', 'numeric', 'between'],
        'datetime' => ['required', 'datetime', 'between'],
        'url'      => ['required', 'url'],
        'text'     => ['required', 'string', 'between'],
    ];

    public readonly string $type;
    public readonly string $max; // FIXME: needs to be string (dates, etc), should not be null, default = 0?
    public readonly string $min; // FIXME: needs to be string (dates, etc), should not be null, default = 0?
    public readonly string $name;
    public readonly ?string $tagGroup;

    public static function fromArray(array $array): static
    {
        return new static(...$array);
    }

    public function __construct(string $type, string $name, int|string|null $min, int|string|null $max, ?string $tagGroup=null)
    {
        $this->type = strtolower($type);
        $this->name = Str::kebab($name);
        $this->min = (string)$min;
        $this->max = (string)$max;
        $this->tagGroup = $tagGroup;

        if (! in_array(strtolower($type), self::TYPES)) {
            throw new InvalidArgumentException(sprintf("The type '$type' is not a valid type. Valid types are: %s.", implode(', ', self::TYPES)));
        }

        if ($max < $min) {
            throw new InvalidArgumentException("The 'max' value cannot be less than the 'min' value.");
        }
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

    protected function parseInt(int|string|null $int): ?int
    {
        if ($int === null) {
            return null;
        }

        return (int) $int;
    }
}
