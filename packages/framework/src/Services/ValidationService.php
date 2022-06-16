<?php

namespace Hyde\Framework\Services;
use Hyde\Framework\Actions\ValidationCheck;
use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Hyde;

/**
 * @see \Hyde\Testing\Feature\Services\ValidationServiceTest
 * @todo Use the model paths instead of the hardcoded paths.
 */
class ValidationService
{
    public static function checks(): array
    {
        return [
            new ValidationCheck('Your site has a 404 page', function () {
                return file_exists(Hyde::path('_pages/404.md'))
                    || file_exists(Hyde::path('_pages/404.blade.php'));
                }, 'Could not find an 404.md or 404.blade.php file!',
                'You can publish the default one using `php hyde publish:views`'
            ),

            new ValidationCheck('Your site has an index page', function () {
                return file_exists(Hyde::path('_pages/index.md'))
                    || file_exists(Hyde::path('_pages/index.blade.php'));
            }, 'Could not find an index.md or index.blade.php file in the _pages directory!',
                'You can publish the one of the built in templates using `php hyde publish:homepage`'
            ),

            new ValidationCheck('A site URL is set', function () {
                return (bool) Hyde::uriPath();
            }, 'Could not find a site URL in the config or .env file!',
            'Adding it may improve SEO as it allows for generating canonical URL, sitemaps, and RSS feeds.'),

            new ValidationCheck('A Torchlight API token is set', function () {
                if (Features::enabled(Features::torchlight())) {
                    return Features::hasTorchlight();
                }
                return null;
            }, 'Torchlight is enabled in the config, but an API token could not be found in the dotenv file.',
            'Torchlight is an API for code syntax highlighting. You can get a free token at torchlight.dev.'),
        ];
    }
}