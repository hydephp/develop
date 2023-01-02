<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models;

use function array_filter;
use function collect;
use function Hyde\evaluate_arrayable;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use Hyde\Framework\Features\Publications\ValidatesPublicationField;
use Hyde\Support\Concerns\Serializable;
use Hyde\Support\Contracts\SerializableContract;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use function str_starts_with;
use function strtolower;

/**
 * Represents an entry in the "fields" array of a publication type schema.
 *
 * @see \Hyde\Framework\Features\Publications\PublicationFieldTypes
 * @see \Hyde\Framework\Testing\Feature\PublicationFieldDefinitionTest
 */
class PublicationFieldDefinition implements SerializableContract
{
    use Serializable;

    public readonly PublicationFieldTypes $type;
    public readonly string $name;
    public readonly array $rules;

    public static function fromArray(array $array): static
    {
        return new static(...$array);
    }

    public function __construct(PublicationFieldTypes|string $type, string $name, array $rules = [])
    {
        $this->type = $type instanceof PublicationFieldTypes ? $type : PublicationFieldTypes::from(strtolower($type));
        $this->name = str_starts_with($name, '__') ? $name : Str::kebab($name);
        $this->rules = $rules;
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type->value,
            'name' => $this->name,
            'rules' => $this->rules,
        ]);
    }

    /**
     * @deprecated Use the ValidatesPublicationField class instead.
     *
     * @param  \Hyde\Framework\Features\Publications\Models\PublicationType|null  $publicationType  Required only when using the 'image' type.
     */
    public function getValidationRules(?PublicationType $publicationType = null): Collection
    {
        return (new ValidatesPublicationField($publicationType, $this))->getValidationRules();
    }

    /**
     * @deprecated Use the ValidatesPublicationField class instead.
     *
     * @param  \Hyde\Framework\Features\Publications\Models\PublicationType|null  $publicationType  Required only when using the 'image' type.
     */
    public function validate(mixed $input = null, Arrayable|array|null $fieldRules = null, ?PublicationType $publicationType = null): array
    {
        $rules = evaluate_arrayable($fieldRules ?? $this->getValidationRules($publicationType));

        return validator([$this->name => $input], [$this->name => $rules])->validate();
    }
}
