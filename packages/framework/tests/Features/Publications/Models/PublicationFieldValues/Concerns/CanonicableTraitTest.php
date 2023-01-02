<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Features\Publications\Models\PublicationFieldValues\Concerns;

use Hyde\Framework\Features\Publications\Models\PublicationFields\Concerns\CanonicableTrait;
use Hyde\Framework\Features\Publications\Models\PublicationFields\Contracts\Canonicable;
use Hyde\Framework\Features\Publications\Models\PublicationFields\PublicationField;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use Hyde\Testing\TestCase;
use RuntimeException;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFields\Concerns\CanonicableTrait
 */
class CanonicableTraitTest extends TestCase
{
    public function testGetCanonicalValue()
    {
        $class = new CanonicableTraitTestClass('foo');

        $this->assertSame('foo', $class->getCanonicalValue());
    }

    public function testGetCanonicalValueTruncatesValuesLongerThan64Characters()
    {
        $class = new CanonicableTraitTestClass(str_repeat('a', 65));

        $this->assertSame(str_repeat('a', 64), $class->getCanonicalValue());
        $this->assertSame(64, strlen($class->getCanonicalValue()));
    }

    public function testCanonicableValueCannotBeEmpty()
    {
        $class = new CanonicableTraitTestClass('');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Canonical value cannot be empty');

        $class->getCanonicalValue();
    }
}

class CanonicableTraitTestClass extends PublicationField implements Canonicable
{
    public const TYPE = PublicationFieldTypes::String;

    use CanonicableTrait;
}
