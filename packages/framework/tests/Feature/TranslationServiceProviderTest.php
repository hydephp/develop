<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;
use Hyde\Foundation\Providers\TranslationServiceProvider;

/**
 * Tests the translation service provider directly, as it only runs once when the application
 * boots, before a test can configure languages through the normal config() helper, so the
 * branch it takes when the site is localized is never exercised by booting the app.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(TranslationServiceProvider::class)]
class TranslationServiceProviderTest extends TestCase
{
    public function testDefaultLocaleFollowsTheAppLocaleWhenLocalizationIsDisabled()
    {
        config(['localization.languages' => []]);
        config(['app.locale' => 'fr']);

        (new TranslationServiceProvider($this->app))->register();

        $this->assertSame('fr', config('app.locale'));
    }

    public function testDefaultLocaleFollowsTheFirstConfiguredLanguageWhenLocalizationIsEnabled()
    {
        config(['localization.languages' => ['de', 'en']]);
        config(['app.locale' => 'fr']);

        (new TranslationServiceProvider($this->app))->register();

        // The first configured language is the default one, and takes precedence over the
        // app locale, which is only meaningful when the site is not localized at all.
        $this->assertSame('de', config('app.locale'));
    }

    public function testFallbackLocaleDefaultsToTheResolvedLocale()
    {
        config(['localization.languages' => ['de', 'en']]);
        config(['app.fallback_locale' => null]);

        (new TranslationServiceProvider($this->app))->register();

        $this->assertSame('de', config('app.fallback_locale'));
    }

    public function testFallbackLocaleIsNotOverriddenWhenAlreadyConfigured()
    {
        config(['localization.languages' => ['de', 'en']]);
        config(['app.fallback_locale' => 'fr']);

        (new TranslationServiceProvider($this->app))->register();

        $this->assertSame('fr', config('app.fallback_locale'));
    }
}
