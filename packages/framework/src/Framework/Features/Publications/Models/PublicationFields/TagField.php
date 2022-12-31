<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFields;

use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use function is_array;

final class TagField extends PublicationField
{
    public const TYPE = PublicationFieldTypes::Tag;

    public function __construct(string|array $value = null)
    {
        parent::__construct();

        if ($value !== null) {
            $this->value = self::parseInput($value);
        }
    }

    protected static function parseInput(string|array $input): array
    {
        if (is_array($input)) {
            return $input;
        }

        return (array) $input;
    }
}
