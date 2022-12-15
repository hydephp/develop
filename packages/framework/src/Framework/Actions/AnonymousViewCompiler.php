<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Concerns\InvokableAction;


/**
 * Compile any Blade file using the Blade facade as it allows us to render
 * it without having to register the directory with the view finder.
 */
class AnonymousViewCompiler extends InvokableAction
{
    //
}
