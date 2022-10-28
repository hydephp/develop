<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Framework\Feature;

use Hyde\Framework\Features\Session\Session;
use Hyde\Testing\TestCase;
use function app;

/**
 * @covers \Hyde\Framework\Features\Session\Session
 * @covers \Hyde\Framework\Features\Session\SessionServiceProvider
 */
class SessionTest extends TestCase
{
    public function test_session_is_bound_to_service_container_as_singleton()
    {
        $this->assertInstanceOf(Session::class, $this->app->make(Session::class));
        $this->assertSame(app(Session::class), $this->app->make(Session::class));
    }

    public function test_session_can_add_warning()
    {
        $session = app(Session::class);

        $session->addWarning('warning');

        $this->assertSame(['warning'], $session->getWarnings());
    }
}
