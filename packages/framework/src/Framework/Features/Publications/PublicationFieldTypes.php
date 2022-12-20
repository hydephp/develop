<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications;

use BadMethodCallException;
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
            self::String => ['required', 'string', 'between'],
            self::Boolean => ['required', 'boolean'],
            self::Integer => ['required', 'integer', 'between'],
            self::Float => ['required', 'numeric', 'between'],
            self::Datetime => ['required', 'datetime', 'between'],
            self::Url => ['required', 'url'],
            self::Text => ['required', 'string', 'between'],
            self::Array => throw new BadMethodCallException('This type has no validation rules'),
            self::Image => throw new BadMethodCallException('This type has no validation rules'),
            self::Tag => throw new BadMethodCallException('This type has no validation rules'),
        };
    }
}
