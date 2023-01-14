<?php

declare(strict_types=1);

namespace Hyde\Publications\Validation;

use Illuminate\Contracts\Validation\InvokableRule;
use function in_array;

/**
 * Extended boolean rule that allows for 'true' and 'false' strings in order to support console inputs.
 *
 * @see https://github.com/illuminate/validation/blob/3f63f1046f67377a64779baaa86d7f1997b5f748/Concerns/ValidatesAttributes.php#L448-L453
 *
 * @todo See if we can set this dynamically in the validating command.
 */
class BooleanRule implements InvokableRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure  $fail
     */
    public function __invoke($attribute, $value, $fail): void
    {
        $acceptable = ['true', 'false', true, false, 0, 1, '0', '1'];

        if (! in_array($value, $acceptable, true)) {
            $fail('The :attribute must be true or false');
        }
    }
}
