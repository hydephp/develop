<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Providers\TranslationServiceProvider;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Providers\TranslationServiceProvider
 */
class TranslationServiceProviderTest extends TestCase
{
    public function testRegister()
    {
        (new TranslationServiceProvider($this->app))->register();

        $this->assertSame('en', config('app.locale'));
        $this->assertSame('en', config('app.fallback_locale'));
    }

    public function testBoot()
    {
        (new TranslationServiceProvider($this->app))->boot();

        $this->assertTrue(true);
    }
}
