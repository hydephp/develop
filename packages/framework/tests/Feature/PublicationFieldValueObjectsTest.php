<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\PublicationFieldValue;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFieldValues\PublicationFieldValue
 */
class PublicationFieldValueObjectsTest extends TestCase
{
    //
}

class TestValue extends PublicationFieldValue
{
    public static function parseInput(string $input): string
    {
        return $input;
    }

    public static function toYamlType(string $input): string
    {
        return $input;
    }
}
