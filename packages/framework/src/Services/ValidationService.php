<?php

namespace Hyde\Framework\Services;
use Hyde\Framework\Actions\ValidationCheck;
use Hyde\Framework\Hyde;

/**
 * @see \Hyde\Testing\Feature\Services\ValidationServiceTest
 */
class ValidationService
{
    public static function checks(): array
    {
        return [
            new ValidationCheck('Your site has a 404 page', function () {
                return file_exists(Hyde::path('_pages/404.md'))
                    || file_exists(Hyde::path('_pages/404.blade.php'));
                },
                'Could not find an 404.md or 404.blade.php file!',
                'You can publish the default one using `php hyde publish:views`'
            ),

            new ValidationCheck('True is false', function () {
                return true === false;
            }, 'Apparently the world makes sense today!'),
        ];
    }
}