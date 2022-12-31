<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFieldValues;

use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\Concerns\CanonicableTrait;
use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\Contracts\Canonicable;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;

final class StringField extends PublicationFieldValue implements Canonicable
{
    use CanonicableTrait;

    public const TYPE = PublicationFieldTypes::String;

    protected static function parseInput(string $input): string
    {
        return $input;
    }
}
