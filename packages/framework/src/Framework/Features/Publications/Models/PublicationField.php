<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models;

use function collect;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use Hyde\Framework\Features\Publications\PublicationService;
use Hyde\Support\Concerns\Serializable;
use Hyde\Support\Contracts\SerializableContract;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use InvalidArgumentException;
use JetBrains\PhpStorm\Deprecated;
use Rgasch\Collection\Collection;
use function strtolower;

/**
 * Represents an entry in the "fields" array of a publication type schema.
 *
 * @see \Hyde\Framework\Features\Publications\PublicationFieldTypes
 * @see \Hyde\Framework\Testing\Feature\PublicationFieldTest
 */
class PublicationField implements SerializableContract
{
    use Serializable;

    public readonly PublicationFieldTypes $type;
    /** @deprecated https://github.com/hydephp/develop/pull/685#issuecomment-1361565809 */
    public readonly string $max;
    /** @deprecated https://github.com/hydephp/develop/pull/685#issuecomment-1361565809 */
    public readonly string $min;
    public readonly string $name;
    public readonly ?string $tagGroup;
    public readonly ?PublicationType $publicationType; // Only used for validation command, interactive command doesn't need this

    public static function fromArray(array $array): static
    {
        return new static(...$array);
    }

    public function __construct(PublicationFieldTypes|string $type, string $name, #[Deprecated] int|string|null $min = '0', #[Deprecated] int|string|null $max = '0', ?string $tagGroup = null, PublicationType $publicationType = null)
    {
        $this->type = $type instanceof PublicationFieldTypes ? $type : PublicationFieldTypes::from(strtolower($type));
        $this->name = Str::kebab($name);
        $this->min = (string) $min;
        $this->max = (string) $max;
        $this->tagGroup = $tagGroup;
        $this->publicationType = $publicationType;

        if ($max < $min && $max !== '0') {
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

    /**
     * @see \Hyde\Framework\Testing\Unit\PublicationFieldTypeValidationRulesTest
     * @see https://laravel.com/docs/9.x/validation#available-validation-rules
     */
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
                $fieldRules->add('array');
                break;
            case 'datetime':
                $fieldRules->add('date');
                if ($this->min) {
                    $dateMin = Carbon::parse($this->min);
                    $fieldRules->add("after:$dateMin");
                }
                if ($this->max) {
                    $dateMax = Carbon::parse($this->max);
                    $fieldRules->add("before:$dateMax");
                }
                break;
            case 'float':
                $fieldRules->add('numeric');
                if ($useRange) {
                    $fieldRules->add("between:$this->min,$this->max");
                }
                break;
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
                $fieldRules->add("in:$valueList");
                break;
            case 'tag':
                $tagValues = PublicationService::getValuesForTagName($this->tagGroup, $reload) ?? collect([]);
                $valueList = $tagValues->implode(',');
                $fieldRules->add("in:$valueList");
                break;
            case 'url':
                $fieldRules->add('url');
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
