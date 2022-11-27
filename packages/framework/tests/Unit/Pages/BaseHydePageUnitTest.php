<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Pages\Concerns\HydePage;
use Hyde\Testing\TestCase;

/**
 * This extendable base text class provides dynamic unit testing for the specified page class.
 */
abstract class BaseHydePageUnitTest extends TestCase
{
    /**
     * @var class-string<\Hyde\Pages\Concerns\HydePage>
     */
    protected static string $page = HydePage::class;
}
