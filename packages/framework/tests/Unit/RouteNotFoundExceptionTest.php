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
    public function test_it_can_be_instantiated()
    {
        $exception = new RouteNotFoundException();

        $this->assertInstanceOf(RouteNotFoundException::class, $exception);
    }

    public function test_it_throws_an_exception_when_page_type_is_not_supported()
    {
        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessage('Route [not-found] not found.');
        $this->expectExceptionCode(404);

        throw new RouteNotFoundException('not-found');
    }

    public function test_it_throws_an_exception_with_custom_message()
    {
        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessage('Custom message');
        $this->expectExceptionCode(404);

        throw new RouteNotFoundException(null, 'Custom message');
    }
}
