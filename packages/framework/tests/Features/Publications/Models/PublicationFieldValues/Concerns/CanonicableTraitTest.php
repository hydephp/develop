<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Features\Publications\Models\PublicationFieldValues\Concerns;

use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\Concerns\CanonicableTrait;
use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\Contracts\Canonicable;
use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\PublicationFieldValue;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFieldValues\Concerns\CanonicableTrait
 */
class CanonicableTraitTest extends TestCase
{
    public function test__toString()
    {
        //
    }

    public function testGetCanonicalValue()
    {
        //
    }
}

class CanonicableTraitTestClass extends PublicationFieldValue implements Canonicable
{
    use CanonicableTrait;

    protected static function parseInput(string $input): string
    {
        return $input;
    }
}
