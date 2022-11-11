<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing;

use Hyde\Testing\TestCase;

class ConfigurableSourceRootsFeatureTest extends TestCase
{
    public function test_default_config_value_is_empty_string()
    {
        $this->assertSame('', config('hyde.source_root'));
    }
}
