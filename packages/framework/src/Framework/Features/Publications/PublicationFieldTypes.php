<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications;

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
    case Boolean = 'boolean';
    case Integer = 'integer';
    case Float = 'float';
    case Datetime = 'datetime';
    case Url = 'url';
    case Array = 'array';
    case Text = 'text';
    case Image = 'image';
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

    public static function getRules(self $type): array
    {
        /** @noinspection PhpDuplicateMatchArmBodyInspection */
        return match ($type) {
            self::String => ['string'],
            self::Boolean => ['boolean'],
            self::Integer => ['integer', 'numeric'],
            self::Float => ['numeric'],
            self::Datetime => ['date'],
            self::Url => ['url'],
            self::Text => ['string'],
            self::Array => ['array'],
            self::Image => [],
            self::Tag => [],
        };
    }
}
