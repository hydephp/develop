<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Features\Publications\Models\PublicationFieldValues\Concerns;

use Hyde\Framework\Features\Publications\Models\PublicationFields\Concerns\CanonicableTrait;
use Hyde\Framework\Features\Publications\Models\PublicationFields\Contracts\Canonicable;
use Hyde\Framework\Features\Publications\Models\PublicationFields\PublicationField;
use Hyde\Testing\TestCase;
use RuntimeException;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFields\Concerns\CanonicableTrait
 */
class CanonicableTraitTest extends TestCase
{
    public function test__toString()
    {
        $class = new CanonicableTraitTestClass('foo');

        $this->assertSame('foo', (string) $class);
    }

    public function testGetCanonicalValue()
    {
        $class = new CanonicableTraitTestClass('foo');

        $this->assertSame('foo', $class->getCanonicalValue());
    }

    public function test__toStringReturnsGetCanonicalValue()
    {
        $class = new CanonicableTraitTestClass('foo');

        $this->assertSame($class->getCanonicalValue(), $class->__toString());
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
    use CanonicableTrait;

    protected static function parseInput(string $input): string
    {
        return $input;
    }
}
