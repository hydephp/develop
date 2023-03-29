<?php

declare(strict_types=1);

namespace Hyde\Publications;

use Illuminate\Support\Collection;

use function collect;
use function in_array;

/**
 * The supported field types for publication types.
 *
 * @see \Hyde\Publications\Models\PublicationFieldDefinition
 * @see \Hyde\Publications\Testing\Feature\PublicationFieldTypesEnumTest
 */
enum PublicationFieldTypes: string
{
    case String = 'string';
    case Datetime = 'datetime';
    case Boolean = 'boolean';
    case Integer = 'integer';
    case Float = 'float';
    case Array = 'array';
    case Media = 'media';
    case Text = 'text';
    case Tag = 'tag';
    case Url = 'url';

    /** Get the default validation rules for this field type. */
    public function rules(): array
    {
        return self::getRules($this);
    }

    /** @return Collection<array-key, self> */
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

    /** Get the default validation rules for a field type. */
    public static function getRules(self $type): array
    {
        /** @noinspection PhpDuplicateMatchArmBodyInspection */
        return match ($type) {
            self::String => ['string'],
            self::Datetime => ['date'],
            self::Boolean => ['boolean'],
            self::Integer => ['integer'],
            self::Float => ['numeric'],
            self::Array => ['array'],
            self::Media => ['string'],
            self::Text => ['string'],
            self::Tag => [],
            self::Url => ['url'],
        };
    }

    /**
     * The types that can be used for canonical fields (used to generate file names).
     *
     * @return \Hyde\Publications\PublicationFieldTypes[]
     */
    public static function canonicable(): array
    {
        return [
            self::String,
            self::Datetime,
            self::Integer,
            self::Text,
        ];
    }

    /**
     * The types that can be array values.
     *
     * @return \Hyde\Publications\PublicationFieldTypes[]
     */
    public static function arrayable(): array
    {
        return [
            self::Array,
            self::Tag,
        ];
    }

    /**
     * @return bool Can the field type be used for canonical fields?
     */
    public function isCanonicable(): bool
    {
        return in_array($this, self::canonicable());
    }

    /**
     * @return bool Does the field type support arrays?
     */
    public function isArrayable(): bool
    {
        return in_array($this, self::arrayable());
    }
}
