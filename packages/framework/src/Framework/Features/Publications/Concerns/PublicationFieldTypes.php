<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Concerns;

/**
 * The supported field types for publication types.
 *
 * @see \Hyde\Framework\Features\Publications\Models\PublicationFieldType
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

    protected final const DEFAULT_RULES = [
        'string'   => ['required', 'string', 'between'],
        'boolean'  => ['required', 'boolean'],
        'integer'  => ['required', 'integer', 'between'],
        'float'    => ['required', 'numeric', 'between'],
        'datetime' => ['required', 'datetime', 'between'],
        'url'      => ['required', 'url'],
        'text'     => ['required', 'string', 'between'],
    ];

    public function rules(): array
    {
        return self::DEFAULT_RULES[$this->value];
    }
}
