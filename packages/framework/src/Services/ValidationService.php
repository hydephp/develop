<?php

namespace Hyde\Framework\Services;
use Hyde\Framework\Actions\ValidationCheck;

class ValidationService
{
    public static function checks(): array
    {
        return [
            new ValidationCheck('True is true', function () {
                return true === true;
            }, 'How did you get here?'),

            new ValidationCheck('True is false', function () {
                return true === false;
            }, 'Apparently the world makes sense today!'),
        ];
    }
}