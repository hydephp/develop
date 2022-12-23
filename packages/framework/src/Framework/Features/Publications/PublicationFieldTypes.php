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
    case Array = 'array';
    case Boolean = 'boolean';
    case Datetime = 'datetime';
    case Float = 'float';
    case Image = 'image';
    case Integer = 'integer';
    case String = 'string';
    case Tag = 'tag';
    case Text = 'text';
    case Url = 'url';

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
            self::Array => ['array'],
            self::Boolean => ['boolean'],
            self::Datetime => ['date'],
            self::Float => ['numeric'],
            self::Image => [],
            self::Integer => ['integer', 'numeric'],
            self::String => ['string'],
            self::Tag => [],
            self::Text => ['string'],
            self::Url => ['url'],
        };
    }
}
