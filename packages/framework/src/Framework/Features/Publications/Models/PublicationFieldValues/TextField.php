<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFieldValues;

use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\Contracts\Canonicable;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use function trim;

final class TextField extends PublicationFieldValue implements Canonicable
{
    use CanonicableTrait;

    public const TYPE = PublicationFieldTypes::Text;

    protected static function parseInput(string $input): string
    {
        // In order to properly store multi-line text fields as block literals,
        // we need to make sure the string ends with a newline character.

        if (substr_count($input, "\n") > 0) {
            return trim($input, "\r\n")."\n";
        }

        return $input;
    }
}
