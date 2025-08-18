<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Exceptions\RouteNotFoundException;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Framework\Exceptions\RouteNotFoundException
 */
class RouteNotFoundExceptionTest extends UnitTestCase
{
    public function testItCanBeInstantiated()
    {
        $exception = new RouteNotFoundException();

        $this->assertInstanceOf(RouteNotFoundException::class, $exception);
    }

    public function testItThrowsAnExceptionWhenPageTypeIsNotSupported()
    {
        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessage('Route [not-found] not found.');
        $this->expectExceptionCode(404);

        throw new RouteNotFoundException('not-found');
    }
}
