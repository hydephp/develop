<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFieldValues;

use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use Stringable;

final class StringField extends PublicationFieldValue implements Stringable
{
    public const TYPE = PublicationFieldTypes::String;

    protected static function parseInput(string $input): string
    {
        return $input;
    }

    public function __toString(): string
    {
        return $this->getValue();
    }
}
