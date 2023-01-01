<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications;

use Hyde\Framework\Features\Publications\Models\PublicationFields;
use Hyde\Framework\Features\Publications\Models\PublicationFields\PublicationField;
use Hyde\Framework\Features\Publications\Validation\BooleanRule;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The supported field types for publication types.
 *
 * @see \Hyde\Framework\Features\Publications\Models\PublicationFieldDefinition
 * @see \Hyde\Framework\Testing\Feature\PublicationFieldTypesEnumTest
 */
enum PublicationFieldTypes: string
{
    case String = PublicationFields\StringField::class;
    case Datetime = PublicationFields\DatetimeField::class;
    case Boolean = PublicationFields\BooleanField::class;
    case Integer = PublicationFields\IntegerField::class;
    case Float = PublicationFields\FloatField::class;
    case Image = PublicationFields\ImageField::class; // TODO Rename to media and move down in the list
    case Array = PublicationFields\ArrayField::class;
    case Text = PublicationFields\TextField::class;
    case Url = PublicationFields\UrlField::class;
    case Tag = PublicationFields\TagField::class; // TODO What is the benefit of having this as a field type as opposed to using tags as a data source of filling in array values? Do users gain any benefit from enforcing the tag values?

    /** @deprecated Is only used in tests, and the related method will be moved to the value classes */
    public function rules(): array
    {
        return self::getRules($this);
    }

    public static function collect(): Collection
    {
        return collect(self::cases());
    }

    public static function values(): array
    {
        return self::collect()->pluck('value')->toArray();
    }

    public static function names(): array
    {
        return self::collect()->pluck('name')->toArray();
    }

    /** @deprecated Will be moved to the value classes */
    public static function getRules(self $type): array
    {
        /** @noinspection PhpDuplicateMatchArmBodyInspection */
        return match ($type) {
            self::String => ['string'],
            self::Datetime => ['date'],
            self::Boolean => [new BooleanRule],
            self::Integer => ['integer', 'numeric'],
            self::Float => ['numeric'],
            self::Image => [],
            self::Array => ['array'],
            self::Text => ['string'],
            self::Url => ['url'],
            self::Tag => [],
        };
    }

    /** @return class-string<\Hyde\Framework\Features\Publications\Models\PublicationFields\PublicationField> */
    public function fieldClass(): string
    {
        $namespace = Str::beforeLast(PublicationField::class, '\\');

        return "$namespace\\{$this->name}Field";
    }
}
