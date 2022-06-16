<?php

namespace Hyde\Framework\Services;
use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\ValidationResult;

/**
 * @see \Hyde\Testing\Feature\Services\ValidationServiceTest
 * @todo Use the model paths instead of the hardcoded paths.
 */
class ValidationService
{
    public static function checks(): array
    {
        $service = new static();
        $checks = [];
        foreach (get_class_methods($service) as $method) {
            if (str_starts_with($method, 'check_')) {
                $checks[] = $method;
            }
        }

        return $checks;
    }

    public function run(string $check): ValidationResult
    {
        return $this->$check(new ValidationResult);
    }

    public function check_site_has_a_404_page(ValidationResult $result): ValidationResult
    {
        if ((file_exists(Hyde::path('_pages/404.md')) || file_exists(Hyde::path('_pages/404.blade.php')))) {
            return $result->pass('Your site has a 404 page');
        }

        return $result->fail('Could not find an 404.md or 404.blade.php file!')
                ->withTip('You can publish the default one using `php hyde publish:views`');
    }

    public function check_site_has_an_index_page(ValidationResult $result): ValidationResult
    {
        if (file_exists(Hyde::path('_pages/index.md')) || file_exists(Hyde::path('_pages/index.blade.php'))) {
            return $result->pass('Your site has an index page');
        }

        return $result->fail('Could not find an index.md or index.blade.php file in the _pages directory!')
                ->withTip('You can publish the one of the built in templates using `php hyde publish:homepage`');
    }

    public function check_site_has_an_app_css_stylesheet(ValidationResult $result): ValidationResult
    {
        if (file_exists(Hyde::path('_site/media/app.css')) || file_exists(Hyde::path('_media/app.css'))) {
            return $result->pass('Your site has an app.css stylesheet');
        }

        return $result->fail('Could not find an app.css file in the _site/media or _media directory!')
            ->withTip('You may need to run `npm run dev`.`');
    }
    
    public function check_site_has_a_base_url_set(ValidationResult $result): ValidationResult
    {
        if ((bool) Hyde::uriPath() === true) {
            return $result->pass('Your site has a base URL set')
                ->withTip('This will allow Hyde to generate canonical URLs, sitemaps, RSS feeds, and more.');
        }

        return $result->fail('Could not find a site URL in the config or .env file!')
                ->withTip('Adding it may improve SEO as it allows Hyde to generate canonical URLs, sitemaps, and RSS feeds');
    }

    public function check_a_torchlight_api_token_is_set(ValidationResult $result): ValidationResult
    {
        if (! Features::enabled(Features::torchlight())) {
           return $result->skip('Check a Torchlight API token is set')
               ->withTip('Torchlight is an API for code syntax highlighting. You can enable it in the Hyde config.');
       }

       if ( Features::hasTorchlight()) {
            return $result->pass('Your site has a Torchlight API token set');
       }

        return $result->fail('Torchlight is enabled in the config, but an API token could not be found in the .env file!')
            ->withTip('Torchlight is an API for code syntax highlighting. You can get a free token at torchlight.dev.');
    }

}
