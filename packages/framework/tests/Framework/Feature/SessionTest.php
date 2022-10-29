<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Framework\Feature;

use function app;
use Hyde\Framework\Features\Session\Session;
use Hyde\Testing\TestCase;

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

    public function test_session_can_store_and_retrieve_data()
    {
        $this->assertFalse(app(Session::class)->has('foo'));

        app(Session::class)->put('foo', 'bar');

        $this->assertTrue(app(Session::class)->has('foo'));
        $this->assertEquals('bar', app(Session::class)->get('foo'));
    }

    public function test_session_can_forget_data()
    {
        $session = new Session();

        $session->put('foo', 'bar');
        $this->assertTrue($session->has('foo'));

        $session->forget('foo');
        $this->assertFalse($session->has('foo'));
    }

    public function test_session_can_add_warning()
    {
        app(Session::class)->addWarning('warning');

        $this->assertSame(['warning'], app(Session::class)->getWarnings());
    }

    public function test_session_is_not_persisted()
    {
        $this->assertSame([], app(Session::class)->getWarnings());
    }

    public function test_session_can_check_if_warnings_are_present()
    {
        $session = app(Session::class);

        $this->assertFalse($session->hasWarnings());

        $session->addWarning('warning');

        $this->assertTrue($session->hasWarnings());
    }
}
