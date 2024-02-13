<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Exceptions\UnsupportedPageTypeException;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Framework\Exceptions\UnsupportedPageTypeException
 */
class UnsupportedPageTypeExceptionTest extends UnitTestCase
{
    public function testItCanBeInstantiated()
    {
        $exception = new UnsupportedPageTypeException();

        $this->assertInstanceOf(UnsupportedPageTypeException::class, $exception);
    }

    public function testItThrowsAnExceptionWhenPageTypeIsNotSupported()
    {
        $this->expectException(UnsupportedPageTypeException::class);
        $this->expectExceptionMessage('The page type [unsupported] is not supported.');

        throw new UnsupportedPageTypeException('unsupported');
    }
}
