<?php

declare(strict_types=1);

namespace Hyde\Framework\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

use function config;

class TranslationServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        // Todo check this doesn't interfere with overrides and/or other packages
        $this->app->useLangPath(__DIR__ . '/../../../resources/lang');

        config([
           'app.locale' => config('app.locale', 'en'),
           'app.fallback_locale' => config('app.fallback_locale', 'en'),
        ]);
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../../../resources/lang/en/validation.php', 'validation');
    }

    public function provides(): array
    {
        return ['translator', 'translation.loader'];
    }
}
