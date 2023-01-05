<?php

declare(strict_types=1);

namespace Hyde\Pages;

use Hyde\Pages\Concerns\HydePage;

/**
 * A virtual page is a page that does not have a source file.
 */
class VirtualPage extends HydePage
{
    public function compile(): string
    {
        // TODO: Implement compile() method.
    }
}
