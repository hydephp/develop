<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

class BooleanRule implements InvokableRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        //
    }
}
