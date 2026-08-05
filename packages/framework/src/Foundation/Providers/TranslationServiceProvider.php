<?php

declare(strict_types=1);

namespace Hyde\Foundation\Providers;

use Hyde\Facades\Config;
use Hyde\Facades\Localization;
use Illuminate\Translation\TranslationServiceProvider as IlluminateTranslationServiceProvider;

/**
 * Register the Hyde translation services.
 *
 * Translation strings are loaded from the lang/{language} directories in the project root,
 * and can be used in any page or component using the standard Laravel __() helper.
 */
class TranslationServiceProvider extends IlluminateTranslationServiceProvider
{
    public function register(): void
    {
        // The translator resolves its locale from the app config, which for a localized site
        // defaults to the first configured language. The static site builder then swaps the
        // locale for each page it compiles, so that each language gets its own strings.

        $language = Config::getNullableString('app.locale') ?? Localization::defaultLanguage();

        $this->app['config']->set('app.locale', $language);
        $this->app['config']->set('app.fallback_locale', Config::getNullableString('app.fallback_locale') ?? $language);

        parent::register();
    }
}
