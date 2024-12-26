<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Console\Helpers;

use Hyde\Testing\UnitTestCase;
use Hyde\Console\Helpers\ViewPublishGroup;

/**
 * @covers \Hyde\Console\Helpers\ViewPublishGroup
 */
class ViewPublishGroupTest extends UnitTestCase
{
    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    protected function setUp(): void
    {
        //
    }
}
