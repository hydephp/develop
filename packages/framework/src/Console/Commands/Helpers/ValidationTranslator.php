<?php

declare(strict_types=1);

namespace Hyde\Console\Commands\Helpers;

use function __;

/**
 * @see \Illuminate\Translation\Translator
 */
class ValidationTranslator
{
    /**
     * @param string $name The name of the attribute being validated.
     * @param string $error The validation error key, for example "validation.required".
     */
    public static function translate(string $name, string $error): string
    {
        return __($error, [
            'attribute' => $name,
        ]);
    }
}
