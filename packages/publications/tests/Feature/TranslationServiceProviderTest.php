<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Publications\Providers\TranslationServiceProvider;
use Hyde\Testing\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Publications\Providers\TranslationServiceProvider::class)]
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

    public function testValidation()
    {
        $this->assertSame('The :attribute must be accepted.', __('validation.accepted'));
    }
}
