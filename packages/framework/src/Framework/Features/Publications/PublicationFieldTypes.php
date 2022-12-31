<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications;

use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\PublicationFieldValue;
use Hyde\Framework\Features\Publications\Validation\BooleanRule;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
    case Image = 'image'; // TODO Rename to media and move down in the list
    case Array = 'array';
    case Text = 'text';
    case Url = 'url';
    case Tag = 'tag'; // TODO What is the benefit of having this as a field type as opposed to using tags as a data source of filling in array values? Do users gain any benefit from enforcing the tag values?

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
            self::Image => [],
            self::Array => ['array'],
            self::Text => ['string'],
            self::Url => ['url'],
            self::Tag => [],
        };
    }

    public function fieldClass(): string
    {
        $namespace = Str::beforeLast(PublicationFieldValue::class, '\\');

        return "$namespace\\{$this->name}Field";
    }
}
