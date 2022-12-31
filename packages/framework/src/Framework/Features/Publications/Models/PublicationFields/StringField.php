<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models\PublicationFields;

use Hyde\Framework\Features\Publications\Models\PublicationFields\Concerns\CanonicableTrait;
use Hyde\Framework\Features\Publications\Models\PublicationFields\Contracts\Canonicable;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;

final class StringField extends PublicationField implements Canonicable
{
    use CanonicableTrait;

    public const TYPE = PublicationFieldTypes::String;

    protected static function parseInput(string $input): string
    {
        return $input;
    }
}
