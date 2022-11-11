<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing;

use Hyde\Testing\TestCase;

/**
 * Test the overall functionality of the configurable source roots feature.
 *
 * Also see these tests which cover specific implementation details:
 *
 * @see \Hyde\Framework\Testing\Feature\HydeKernelTest
 * @see \Hyde\Framework\Testing\Unit\HydeServiceProviderTest
 */
class ConfigurableSourceRootsFeatureTest extends TestCase
{
    public function test_default_config_value_is_empty_string()
    {
        $this->assertSame('', config('hyde.source_root'));
    }
}
