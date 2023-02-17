<?php

declare(strict_types=1);

namespace Hyde\Pages\Concerns;

use Hyde\Support\Contracts\DiscoverableContract;

/**
 * This class implements the DiscoverableContract interface,
 * and is used by auto-discoverable HydePage classes.
 */
abstract class DiscoverablePage extends HydePage implements DiscoverableContract
{
    //
}
