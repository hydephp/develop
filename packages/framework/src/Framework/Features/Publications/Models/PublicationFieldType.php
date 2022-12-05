<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models;

use Hyde\Framework\Features\Publications\Concerns\PublicationFieldTypes;
use Hyde\Support\Concerns\Serializable;
use Hyde\Support\Contracts\SerializableContract;
use Illuminate\Support\Str;
use InvalidArgumentException;
use function strtolower;

/**
 * Represents an entry in the "fields" array of a publication type schema.
 *
 * @see \Hyde\Framework\Features\Publications\Concerns\PublicationFieldTypes
 * @see \Hyde\Framework\Testing\Feature\PublicationFieldTypeTest
 */
class PublicationFieldType implements SerializableContract
{
    use Serializable;

    public readonly PublicationFieldTypes $type;
    public readonly string $max;
    public readonly string $min;
    public readonly string $name;
    public readonly ?string $tagGroup;
    public readonly ?PublicationType $publicationType; // Only used for validation command, interactive command doesn't need this

    public static function fromArray(array $array): static
    {
        return new static(...$array);
    }

    public function __construct(string $type, string $name, int|string|null $min, int|string|null $max, ?string $tagGroup = null, PublicationType $publicationType = null)
    {
        $this->type = PublicationFieldTypes::from(strtolower($type));
        $this->name = Str::kebab($name);
        $this->min = (string) $min;
        $this->max = (string) $max;
        $this->tagGroup = $tagGroup;
        $this->publicationType = $publicationType;

        if ($max < $min) {
            throw new InvalidArgumentException("The 'max' value cannot be less than the 'min' value.");
        }
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'name' => $this->name,
            'min'  => $this->min,
            'max'  => $this->max,
            'tagGroup' => $this->tagGroup,
        ];
    }

    public function validateInputAgainstRules(string $input): bool
    {
        // TODO: Implement this method.

        return true;
    }
}
