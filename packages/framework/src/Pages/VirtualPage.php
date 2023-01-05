<?php

declare(strict_types=1);

namespace Hyde\Pages;

use Hyde\Pages\Concerns\HydePage;

/**
 * A virtual page is a page that does not have a source file.
 *
 * This can be useful for creating pagination pages and the like.
 * When used in a package, it's on the package developer to ensure
 * that the virtual page is registered with Hyde, usually within the
 * boot method of the package's service provider so it can be compiled.
 */
class VirtualPage extends HydePage
{
    public function compile(): string
    {
        // TODO: Implement compile() method.
    }
}
