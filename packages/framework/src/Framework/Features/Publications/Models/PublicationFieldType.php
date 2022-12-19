<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models;

use function collect;
use Hyde\Framework\Features\Publications\Concerns\PublicationFieldTypes;
use Hyde\Framework\Features\Publications\PublicationService;
use Hyde\Support\Concerns\Serializable;
use Hyde\Support\Contracts\SerializableContract;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Rgasch\Collection\Collection;
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

    /**
     * @todo add default values for min and max arguments
     * @todo allow enum cases to be passed to the type argument
     */
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

    /** @see \Hyde\Framework\Testing\Unit\PublicationFieldTypeValidationRulesTest */
    public function getValidationRules(bool $reload = true): Collection
    {
        $defaultRules = Collection::create(PublicationFieldTypes::values());
        $fieldRules = Collection::create($defaultRules->get($this->type->value));

        $useRange = true;
        // The trim command used to process the min/max input results in a string, so
        // we need to test both int and string values to determine required status.
        if (($this->min && ! $this->max) || ($this->min == '0' && $this->max == '0')) {
            $fieldRules->forget($fieldRules->search('required'));
            $useRange = false;
        }

        switch ($this->type->value) {
            case 'array':
                $fieldRules->add('array'); // FIXME do we do range validation too?
                break;
            case 'datetime':
                if ($useRange) {
                    $fieldRules->add("after:$this->min");
                    $fieldRules->add("before:$this->max");
                }
                break;
            case 'float':
            case 'integer':
            case 'string':
            case 'text':
                if ($useRange) {
                    $fieldRules->add("between:$this->min,$this->max");
                }
                break;
            case 'image':
                $mediaFiles = PublicationService::getMediaForPubType($this->publicationType, $reload);
                $valueList = $mediaFiles->implode(',');
                $fieldRules->add("in:$valueList"); // FIXME What if the list is empty?
                                                   // FIXME: Now the items look like 'in:_media/foo/bar.jpg', but do we really need the directory information?
                                                   //   Wouldn't it suffice with just 'in:bar.jpg' since we already know what directory it is in?
                                                   //   We could then easily qualify it within the template and/or via a helper method.
                break;
            case 'tag':
                $tagValues = PublicationService::getValuesForTagName($this->tagGroup, $reload) ?? collect([]);
                $valueList = $tagValues->implode(',');
                $fieldRules->add("in:$valueList");
                break;
            case 'url':
                // FIXME Shouldn't we add a 'url' rule here?
                break;
        }

        return $fieldRules;
    }

    public function validate(mixed $input = null, Collection $fieldRules = null): array
    {
        if (! $fieldRules) {
            $fieldRules = $this->getValidationRules(false);
        }

        $validator = validator([$this->name => $input], [$this->name => $fieldRules->toArray()]);

        return $validator->validate();
    }
}
