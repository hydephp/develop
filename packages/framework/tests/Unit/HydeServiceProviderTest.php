<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\HydeServiceProvider;
use Hyde\Testing\TestCase;

/**
 * @todo Improve testing for this class.
 */
class HydeServiceProviderTest extends TestCase
{
    protected HydeServiceProvider $provider;

    public function setUp(): void
    {
        $this->provider = new HydeServiceProvider(app());

        parent::setUp();
    }

    public function test_provider_is_constructed()
    {
        $this->assertInstanceOf(HydeServiceProvider::class, $this->provider);
    }

    public function test_provider_has_register_method()
    {
        $this->assertTrue(method_exists($this->provider, 'register'));
    }

    public function test_provider_has_boot_method()
    {
        $this->assertTrue(method_exists($this->provider, 'boot'));
    }
}
