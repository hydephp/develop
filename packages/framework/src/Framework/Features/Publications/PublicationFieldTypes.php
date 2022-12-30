<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications;

use Hyde\Framework\Features\Publications\Validation\BooleanRule;
use Illuminate\Support\Collection;

/**
 * The supported field types for publication types.
 *
 * @see \Hyde\Framework\Features\Publications\Models\PublicationField
 * @see \Hyde\Framework\Testing\Feature\PublicationFieldTypesEnumTest
 */
enum PublicationFieldTypes: string
{
    case String = 'string';
    case Datetime = 'datetime';
    case Boolean = 'boolean';
    case Integer = 'integer';
    case Float = 'float';
    case Image = 'image';
    case Array = 'array';
    case Text = 'text';
    case Url = 'url';
    case Tag = 'tag';

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

    public static function getRules(self $type): array
    {
        /** @noinspection PhpDuplicateMatchArmBodyInspection */
        return match ($type) {
            self::String => ['string'],
            self::Datetime => ['date'],
            self::Boolean => [new BooleanRule],
            self::Integer => ['integer', 'numeric'],
            self::Float => ['numeric'],
            self::Image => [], // TODO Rename to media and move down in the list
            self::Array => ['array'],
            self::Text => ['string'],
            self::Url => ['url'],
            self::Tag => [], // TODO What is the benefit of having this as a field type as opposed to using tags as a data source of filling in array values? Do users gain any benefit from enforcing the tag values?
        };
    }

    /**
     * The types that can be used for canonical fields (used to generate file names).
     *
     * @return \Hyde\Framework\Features\Publications\PublicationFieldTypes[]
     */
    public static function canonicable(): array
    {
        return [
            self::String,
            self::Integer,
            self::Datetime,
            self::Text,
        ];
    }
}
